/**
 * This javascript file handles user logic for the account page.
 *
 * The account page is used to add, change, or delete API Keys.
 *
 * @link   js/account.js
 * @file   This file is for the account page of the Alt Text Magic plugin. 
 * @author Minegap LLC
 * @since 1.0.0
 */

/**
* DOMContentLoaded callback function.
* 
* @param {Event} e Event object.
* @return {void}
*/
document.addEventListener('DOMContentLoaded', async (e) => {
    // State contains local state for the account page. 
    let state = {
        accountContainer: document.getElementById('load-account'),
        altTextMagicContainer: document.getElementById('altTextMagicContainer'),
        yourPlan: document.getElementById('yourPlan'),
        altTextMagicAPIKeySetContainer: document.getElementById('altTextMagicAPIKeySetContainer'),
        altTextMagicChangeAPIKeyForm: document.getElementById('altTextMagicChangeAPIKeyForm'),
        altTextMagicChangeAPIKeyInput: document.getElementById('altTextMagicChangeAPIKeyInput'),
        altTextMagicChangeAPIKeyButton: document.getElementById('altTextMagicChangeAPIKeyButton'),
        changeAPIKeyInitialContainer: document.getElementById('changeAPIKeyInitialContainer'),
        changeAPIKeyChangeContainer: document.getElementById('changeAPIKeyChangeContainer'),
        submittingChangeAPIKeyForm: false,
        altTextMagicChangeAPIKeyCancelButton: document.getElementById('altTextMagicChangeAPIKeyCancelButton'),
        altTextMagicShowChangeAPIKeyButton: document.getElementById('altTextMagicShowChangeAPIKeyButton'),
        altTextMagicAPIKeyForm: document.getElementById('altTextMagicAPIKeyForm'),
        altTextMagicAPIKeyInput: document.getElementById('altTextMagicAPIKeyInput'),
        altTextMagicAPIKeyButton: document.getElementById('altTextMagicAPIKeyButton'),
        altTextMagicDeleteAPIKeyButton: document.getElementById('altTextMagicDeleteAPIKeyButton'),
        submittingAPIKeyForm: false,
        APIKeyInvalidContainer: document.getElementById('APIKeyInvalidContainer'),
        altTextMagicInvalidAPIKeyForm: document.getElementById('altTextMagicInvalidAPIKeyForm'),
        altTextMagicInvalidAPIKeyInput: document.getElementById('altTextMagicInvalidAPIKeyInput'),
        altTextMagicInvalidAPIKeyButton: document.getElementById('altTextMagicInvalidAPIKeyButton'),
        form: document.getElementById('signUpForm'),
        emailInput: document.getElementById('emailInput'),
        passwordInput: document.getElementById('passwordInput'),
        confirmPasswordInput: document.getElementById('confirmPasswordInput'),
        formErrorAlert: document.getElementById('formErrorAlert'),
        signUpButton: document.getElementById('signUpButton'),
        signUpButtonText: document.getElementById('signUpButtonText'),
        signUpButtonLoadingSpinner: document.getElementById('signUpButtonLoadingSpinner'),
        languagesSelect: document.getElementById('languages-select'),
        altTextMagicChangeSettingsButton: document.getElementById('altTextMagicChangeSettingsButton'),
        altTextMagicGenerateOnUploadCheckbox: document.getElementById('altTextMagicGenerateOnUpload'),
    };

    // POST request to get the user's account information.
    let response = await Promise.resolve(jQuery.post(
        ajaxurl, {
        'action': 'alt_text_magic_get_state',
        nonce: alt_text_magic_nonce_obj.state_nonce,
    }));
    let responseData = JSON.parse(response);

    if (responseData.hasOwnProperty('generate_on_upload')) {
        if (responseData.generate_on_upload === '1') {
            state.altTextMagicGenerateOnUploadCheckbox.checked = true;
        } else if (responseData.generate_on_upload === '0') {
            state.altTextMagicGenerateOnUploadCheckbox.checked = false;
        }
    }

    // Get API Key from the response.
    state.apiKey = null;
    if (responseData.hasOwnProperty('api_key')) {
        state.apiKey = responseData.api_key;
    }

    // Display elements to set the API Key if it has not been set yet.
    if (!state.apiKey) {
        state.altTextMagicContainer.classList.remove('display-none');
        state.accountContainer.classList.remove('display-none');
    }

    // If the API has been set, continue by getting user info.
    if (state.apiKey && state.apiKey !== 'undefined') {
        // POST request to get the user's information.
        let response = await Promise.resolve(jQuery.post(
            ajaxurl, {
            'action': 'alt_text_magic_info',
            nonce: alt_text_magic_nonce_obj.info_nonce,
        }));
        let accountData = JSON.parse(response);
        let responseOK = true;
        if (accountData.hasOwnProperty('success') && !accountData.success) {
            responseOK = false;
        }

        // If the request was not successful, see what the reason was.
        if (!responseOK) {
            if (accountData.message === 'invalid API key') {
                // Change href to plugin page.
                let pluginQueryString = '?page=alt-text-magic-plugin';
                if (!window.location.href.includes(pluginQueryString)) {
                    let reloadURL = window.location.origin + window.location.pathname;
                    window.location.href = reloadURL + pluginQueryString;
                    return;
                }
                state.APIKeyInvalidContainer.classList.remove('display-none');
            }
        } else {
            state.yourPlan.innerText = accountData.account_type;
            state.altTextMagicAPIKeySetContainer.classList.remove('display-none');
        }

        state.accountContainer.classList.remove('display-none');
    }

    if (responseData.hasOwnProperty('language') && responseData.language) {
        state.languagesSelect.value = responseData.language;
    }

    // Language select change event listener.
    state.altTextMagicChangeSettingsButton.addEventListener('click', async (e) => {
        let language = state.languagesSelect.value;

        let response = await Promise.resolve(jQuery.post(
            ajaxurl, {
            action: "alt_text_magic_change_language",
            language: language,
            nonce: alt_text_magic_nonce_obj.change_language_nonce,
        }));
        let responseJSON = JSON.parse(response);
        if (responseJSON.success) {
            toastySuccess("Successfully changed languages.");
        } else {
            toastyError("Unable to change the language, please try again.");
        }
    });

    /**
     * Submit event listener for the set API Key form.
     * 
     * This form is only displayed if the API Key has not been set.
     * 
     * @param {SubmitEvent} e The submit event.
     * @returns {void}
     */
    state.altTextMagicAPIKeyForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // If already submitting this form, return.
        if (state.submittingAPIKeyForm) {
            return;
        }

        // Check to see if the form has been successfully filled out.
        if (!state.altTextMagicAPIKeyForm.checkValidity()) {
            e.stopPropagation();
            state.altTextMagicAPIKeyForm.classList.add('was-validated');
            return;
        }

        state.submittingAPIKeyForm = true;
        state.altTextMagicAPIKeyButton.disabled = true;
        let startText = state.altTextMagicAPIKeyButton.innerText;
        state.altTextMagicAPIKeyButton.innerText = 'Loading...';
        let newAPIKey = state.altTextMagicAPIKeyInput.value;

        // POST request to set the API Key.
        let response = await Promise.resolve(jQuery.post(
            ajaxurl, {
            action: "alt_text_magic_set_api_key",
            apiKey: newAPIKey,
            nonce: alt_text_magic_nonce_obj.set_api_key_nonce,
        }));
        let responseJSON = JSON.parse(response);
        if (responseJSON.success) {
            // The API Key was successfully set, redirect to the account page.
            let reloadURL = window.location.origin + window.location.pathname;
            window.location.href = reloadURL + '?page=alt-text-magic-account';
        } else {
            toastyError("Your API key could not be added. Ensure that your API key is correct and try again.");
            state.submittingAPIKeyForm = false;
            state.altTextMagicAPIKeyButton.disabled = false;
            state.altTextMagicAPIKeyButton.innerText = startText;
        }
    });

    /**
     * Submit event listener for the invalid API Key form.
     * 
     * This form is only displayed if the API Key is invalid.
     * 
     * @param {SubmitEvent} e The submit event.
     * @returns {void}
     */
    state.altTextMagicInvalidAPIKeyForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // If already submitting this form, return.
        if (state.submittingAPIKeyForm) {
            return;
        }

        // Check to see if the form has been successfully filled out.
        if (!altTextMagicInvalidAPIKeyForm.checkValidity()) {
            e.stopPropagation();
            state.altTextMagicInvalidAPIKeyForm.classList.add('was-validated');
            return;
        }

        state.submittingAPIKeyForm = true;
        state.altTextMagicInvalidAPIKeyButton.disabled = true;
        let newAPIKey = state.altTextMagicInvalidAPIKeyInput.value;

        // POST request to set the API Key.
        let response = await Promise.resolve(jQuery.post(
            ajaxurl, {
            action: "alt_text_magic_set_api_key",
            apiKey: newAPIKey,
            nonce: alt_text_magic_nonce_obj.set_api_key_nonce,
        }));
        let responseJSON = JSON.parse(response);
        if (responseJSON.success) {
            // The API Key was successfully set, redirect to the account page.
            let reloadURL = window.location.origin + window.location.pathname;
            window.location.href = reloadURL + '?page=alt-text-magic-account';
        } else {
            toastyError("Your API key could not be added. Ensure that your API key is correct and try again.");
            state.submittingAPIKeyForm = false;
            state.altTextMagicInvalidAPIKeyButton.disabled = false;
        }
    });

    /** 
     * Click event listener for the delete API Key button.
     * 
     * This button is only displayed if the API Key has been successfully set.
     * 
     * @param {MouseEvent} e The click event.
     * @returns {void}
     */
    state.altTextMagicDeleteAPIKeyButton.addEventListener('click', async (e) => {
        e.preventDefault();

        let response = await Promise.resolve(jQuery.post(
            ajaxurl, {
            action: "alt_text_magic_set_api_key",
            apiKey: '',
            nonce: alt_text_magic_nonce_obj.set_api_key_nonce,
        }));
        // Redirect to the plugin page on delete.
        let reloadURL = window.location.origin + window.location.pathname;
        window.location.href = reloadURL + '?page=alt-text-magic-plugin';
    });

    /**
     * Click event listener for the show change API Key button.
     * 
     * This button is only displayed if the API Key has been successfully set.
     * 
     * @param {MouseEvent} e The click event.
     * @returns {void}
     */
    state.altTextMagicShowChangeAPIKeyButton.addEventListener('click', async (e) => {
        state.changeAPIKeyInitialContainer.classList.add('display-none');
        state.changeAPIKeyChangeContainer.classList.remove('display-none');
    });

    /**
     * Click event listener for the cancel change API Key button.
     * 
     * This button is only displayed if the API Key has been successfully set.
     * This hides the change API Key form.
     * 
     * @param {MouseEvent} e The click event.
     * @returns {void}
     */
    state.altTextMagicChangeAPIKeyCancelButton.addEventListener('click', async (e) => {
        e.preventDefault();
        e.stopPropagation();
        state.changeAPIKeyInitialContainer.classList.remove('display-none');
        state.changeAPIKeyChangeContainer.classList.add('display-none');
        state.altTextMagicChangeAPIKeyForm.classList.remove('was-validated');
        state.altTextMagicChangeAPIKeyInput.value = '';
        state.altTextMagicChangeAPIKeyInput.blur();
    });

    /**
     * Submit event listener for the change API Key form.
     * 
     * This form is only displayed if the API Key has been successfully set.
     * 
     * @param {SubmitEvent} e The submit event.
     * @returns {void}
     */
    state.altTextMagicChangeAPIKeyForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        // If already submitting this form, return.
        if (state.submittingChangeAPIKeyForm) {
            return;
        }

        // Check to see if the form has been successfully filled out.
        if (!state.altTextMagicChangeAPIKeyForm.checkValidity()) {
            e.stopPropagation();
            state.altTextMagicChangeAPIKeyForm.classList.add('was-validated');
            return;
        }

        state.submittingChangeAPIKeyForm = true;
        state.altTextMagicChangeAPIKeyButton.disabled = true;
        state.altTextMagicChangeAPIKeyCancelButton.disabled = true;
        let newAPIKey = state.altTextMagicChangeAPIKeyInput.value;

        // POST request to set the API Key.
        let response = await Promise.resolve(jQuery.post(
            ajaxurl, {
            action: "alt_text_magic_set_api_key",
            apiKey: newAPIKey,
            nonce: alt_text_magic_nonce_obj.set_api_key_nonce,
        }));
        let responseJSON = JSON.parse(response);
        if (responseJSON.success) {
            let reloadURL = window.location.origin + window.location.pathname;
            window.location.href = reloadURL + '?page=alt-text-magic-account';
        } else {
            toastyError("Your API key could not be updated. Ensure that your API key is correct and try again.");
            state.submittingChangeAPIKeyForm = false;
            state.altTextMagicChangeAPIKeyButton.disabled = false;
            state.altTextMagicChangeAPIKeyCancelButton.disabled = false;
        }
    });

    /**
     * Change event listener for the generate on upload checkbox.
     * 
     * @param {SubmitEvent} e The change event.
     * @returns {void}
     */
    state.altTextMagicGenerateOnUploadCheckbox.addEventListener('change', async (e) => {
        let val = '1';
        if (!e.target.checked) {
            val = '0';
        }
        let response = await Promise.resolve(jQuery.post(
            ajaxurl, {
            action: "alt_text_magic_set_generate_on_upload",
            generateOnUpload: val,
            nonce: alt_text_magic_nonce_obj.set_generate_on_upload_nonce,
        }));
        let responseJSON = JSON.parse(response);
        if (responseJSON.success) {
            toastySuccess("Generate on upload setting updated.");
        } else {
            toastyError("Generate on upload setting could not be updated.");
        }
    });
});