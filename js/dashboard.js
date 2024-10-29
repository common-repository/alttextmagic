/**
 * This javascript file handles user logic for the dashboard page of the
 * Alt Text Magic plugin.
 *
 * The dashboard page display the user's account status, as well as the user's
 * current credits.
 *
 * @link   js/dashboard.js
 * @file   This file is for the dashboard page.
 * @author Minegap LLC
 * @since 1.0.0
 */

/**
 * Returns the month name for a given date.
 * 
 * The month's name is retrieved by calling getMonth() on the date object.
 * getMonth() returns a number between 0 and 11, where 0 is January and 11 is December.
 * This value is used to index into the monthNames array.
 * 
 * @param {Date}     date   The date to get the month name for.
 * @returns {string}        The month name.
 */
function getMonthName(date) {
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    let monthIdx = date.getMonth();

    if (monthIdx < 0 || monthIdx >= monthNames.length) {
        return "";
    }

    return monthNames[date.getMonth()];
}

/**
* DOMContentLoaded callback function.
* 
* @param {Event} e Event object.
* @return {void}
*/
document.addEventListener('DOMContentLoaded', async (e) => {
    // State contains references to elements used by the dashboard page.
    let state = {
        accountContainer: document.getElementById('load-account'),
        minMonthlyElement: document.getElementById('minMonthly'),
        maxMonthlyElement: document.getElementById('maxMonthly'),
        monthlyPercentageElement: document.getElementById('monthlyPercentage'),
        minBulkElement: document.getElementById('bulkCredits'),
        maxBulkElement: document.getElementById('maxBulk'),
        bulkPercentageElement: document.getElementById('bulkPercentage'),
        yourPlanElement: document.getElementById('yourPlan'),
        maxMonthly2Element: document.getElementById('maxMonthly2'),
        subscriptionDatesElement: document.getElementById('subscriptionDates'),
        bulkCreditsElement: document.getElementById('bulkCredits'),
    };

    // POST request to get the account information.
    let response = await Promise.resolve(jQuery.post(
        ajaxurl, {
        'action': 'alt_text_magic_get_state',
        nonce: alt_text_magic_nonce_obj.state_nonce,
    }));
    let responseData = JSON.parse(response);

    // Get the API Key from the response.
    state.apiKey = null;
    if (responseData.hasOwnProperty('api_key')) {
        state.apiKey = responseData.api_key;
    }

    // If the API Key is set, get the user information.
    if (state.apiKey && state.apiKey !== 'undefined') {
        // POST request to get the user information.
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
        if (!responseOK) {
            // If the request fails, redirect to the plugin page. This usually
            // means the API Key is invalid.
            let reloadURL = window.location.origin + window.location.pathname;
            window.location.href = reloadURL + '?page=alt-text-magic-plugin';
            return;
        }

        // Display the account information.
        let billingDueAt = new Date(accountData.card_info.billing_due_at * 1000);
        let billingStartDate = new Date(accountData.card_info.billing_due_at * 1000);
        billingStartDate.setMonth(billingStartDate.getMonth() - 1);

        let startDateStr = getMonthName(billingStartDate) + ' ' + billingStartDate.getDate() + ', ' + billingStartDate.getFullYear();
        let endDateStr = getMonthName(billingDueAt) + ' ' + billingDueAt.getDate() + ', ' + billingDueAt.getFullYear();
        state.subscriptionDatesElement.innerText = startDateStr + ' - ' + endDateStr;

        state.yourPlanElement.innerHTML = accountData.account_type;
        state.maxMonthly2Element.innerHTML = accountData.monthly_image_limit;

        // Display remaining monthly and a la carte credits.
        state.bulkCreditsElement.innerHTML = accountData.image_credit_limit - accountData.image_credit_count;
        state.minMonthlyElement.innerText = accountData.monthly_image_count;
        state.maxMonthlyElement.innerText = accountData.monthly_image_limit;
        state.monthlyPercentageElement.style.width = (accountData.monthly_image_count / accountData.monthly_image_limit) * 100 + '%';
        state.minBulkElement.innerText = (accountData.image_credit_limit - accountData.image_credit_count);

        // Show elements by setting the container's display property to 'block'.
        state.accountContainer.style.display = 'block';
    }
});