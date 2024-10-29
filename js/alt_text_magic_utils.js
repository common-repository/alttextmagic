/**
 * This javascript file handles common functionality for the Alt Text Magic plugin.
 *
 * Utility functions are defined here, such as functions to display toast messages.
 *
 * @link   js/alt_text_magic_utils.js
 * @file   This file is for the utility functions. 
 * @author Minegap LLC
 * @since 1.0.0
 */

/**
 * Sets loading state for a button.
 * 
 * The button will be disabled, it's text will be hidden,
 * and a loading spinner will be shown.
 * 
 * @param {Element} button                  Button to disable.
 * @param {Element} buttonText              Button's text to hide.
 * @param {Element} buttonLoadingSpinner    Button's loading spinner to show.
 * @return {void} 
 */
function setLoadingButtonIsLoading(button, buttonText, buttonLoadingSpinner) {
    button.disabled = true;
    buttonText.classList.add('invisible');
    buttonLoadingSpinner.classList.remove('display-none');
}

/**
 * Clears loading state for a button.
 * 
 * The button will not be disabled, it's text will be shown,
 * and a loading spinner will be hidden.
 * 
 * @param {Element} button                  Button to set disabled to false for.
 * @param {Element} buttonText              Button's text to show.
 * @param {Element} buttonLoadingSpinner    Button's loading spinner to hide.
 * @return {void}
 */
function setLoadingButtonIsNotLoading(button, buttonText, buttonLoadingSpinner) {
    button.disabled = false;
    buttonText.classList.remove('invisible');
    buttonLoadingSpinner.classList.add('display-none');
}

/**
 * Displays an information toast.
 * 
 * Creates a Toastify object with the given message.
 * 
 * @param {string} message The message to display in the toast.
 * @return {void}
 */
function toastyInfo(message) {
    Toastify({
        text: message,
        duration: 5000,
        close: true,
        gravity: "top",
        position: "center",
        stopOnFocus: true,
        style: {
            background: "#56c45f",
        },
    }).showToast();
}

/**
 * Displays an error toast.
 * 
 * Creates a Toastify object with the given message.
 * 
 * @param {string} message The message to display in the toast.
 * @return {void}
 */
function toastyError(message) {
    Toastify({
        text: message,
        duration: 5000,
        close: true,
        gravity: "top",
        position: "center",
        stopOnFocus: true,
        style: {
            background: "#c45656",
        },
    }).showToast();
}

/**
 * Displays a success toast.
 * 
 * Creates a Toastify object with the given message.
 * 
 * @param {string} message The message to display in the toast.
 * @return {void}
 */
function toastySuccess(message) {
    Toastify({
        text: message,
        duration: 5000,
        close: true,
        gravity: "top",
        position: "center",
        stopOnFocus: true,
        style: {
            background: "#56c45f",
        },
    }).showToast();
}

