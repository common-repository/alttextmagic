/**
 * This javascript file handles user logic for the library updater page.
 *
 * The library updater page allows the user to update alt texts for their
 * images in a batch process.
 *
 * @link   js/library_updater.js
 * @file   This file is for the library updater page.
 * @author Minegap LLC
 * @since 1.0.0
 */


/**
 * Hides an element.
 * 
 * The element is hidden by adding the display-none class to it's classList.
 * 
 * @param {Element} element The element to hide. 
 * @return {void}
 */
function hideElement(element) {
    if (element) {
        element.classList.add('display-none');
    }
}

/**
 * Shows an element.
 * 
 * The element is shown by removing the display-none class from it's classList.
 * 
 * @param {Element} element The element to show.
 * @return {void}
 */
function showElement(element) {
    if (element) {
        element.classList.remove('display-none');
    }
}

/**
 * Updates the display for the Library Updater page.
 * 
 * This is done by performing a POST request to get_state to get
 * the current state of the library updater. The elements in the
 * state parameter are then updated with the values from the response.
 * 
 * @param {Object}  state               The state for the Library updater page.
 * @param {boolean} update_images_info  Whether to update the images info.
 * @return {void}
 */
async function updateDisplay(state, update_images_info, hideBulkSuggestions, isInitialLibraryUpdateCall) {
    // State data that does not rely on a get_state call.
    // Set images missing alt text display.
    let imagesMissingAltText = parseInt(state.imagesMissingAltText);
    // Make sure that imagesMissingAltText is not NaN.
    if (imagesMissingAltText !== NaN) {
        state.missingImages.innerText = state.imagesMissingAltText;
    } else {
        state.missingImages.innerText = '0';
    }

    // A batch update is in progress.
    if (state.batchInProgress) {
        if (isInitialLibraryUpdateCall) {
            state.batchProgressText.innerText = 'Library updater initializing...';
            state.batchRemainingTime.innerText = 'Estimated time remaining: Calculating...';
        } else {
            if (state.batchCurrentIdx > state.batchTotalImages) {
                state.batchCurrentIdx = state.batchTotalImages;
            }
            let totalImagesRemaining = state.batchTotalImages - state.batchCurrentIdx;
            if (totalImagesRemaining < 0) {
                totalImagesRemaining = 0;
            }
            // Estimated time remaining until the batch update is complete.
            // use state.batchTimes a list of numbers, average it
            let estimatedTime = 0;
            if (state.batchTimes.length > 0) {
                estimatedTime = state.batchTimes.reduce((a, b) => a + b, 0) / state.batchTimes.length;
                estimatedTime = estimatedTime * ((state.batchTotalImages - state.batchCurrentIdx) / state.chunkSize);
            } else {
                estimatedTime = 0;
            }

            let estimatedTimeString = 'Calculating...';
            if (estimatedTime > 60) {
                estimatedTimeString = Math.round(estimatedTime / 60) + ' minutes';
            } else if (estimatedTime > 0) {
                estimatedTimeString = Math.round(estimatedTime) + ' seconds';
            }
            state.batchRemainingTime.innerText = 'Estimated time remaining: ' + estimatedTimeString;
            state.batchProgressText.innerText = state.batchCurrentIdx + ' / ' + state.batchTotalImages + ' images processed';
            if (state.batchCurrentIdx === 0) {
                state.batchProgressBar.style.width = '0.5%';
            } else {
                if (state.batchTotalImages === 0) {
                    state.batchProgressBar.style.width = '0.5%';
                } else {
                    state.batchProgressBar.style.width = (state.batchCurrentIdx / state.batchTotalImages) * 100 + '%';
                }
            }
        }
        hideElement(state.batchNotRunning);
        showElement(state.batchProgressBar);
        showElement(state.batchRemainingTime);
        showElement(state.batchProgressText);
        showElement(state.cancelBatchButton);
        showElement(state.batchRunning);
    } else {
        hideElement(state.batchRunning);
        showElement(state.batchNotRunning);
    }

    // POST request to get_state to get the current state of the library updater.
    let response = await Promise.resolve(jQuery.post(
        ajaxurl, {
        'action': 'alt_text_magic_get_state',
        'update_images_info': update_images_info,
        nonce: alt_text_magic_nonce_obj.state_nonce,
    }));
    let accountData = JSON.parse(response);

    // Update imagesMissingAltText for the initial call.
    if (update_images_info) {
        state.imagesMissingAltText = accountData.images_missing_alt_text;
        // Set images missing alt text display.
        let imagesMissingAltText = parseInt(state.imagesMissingAltText);
        // Make sure that imagesMissingAltText is not NaN.
        if (imagesMissingAltText !== NaN) {
            state.missingImages.innerText = state.imagesMissingAltText;
        } else {
            state.missingImages.innerText = '0';
        }
    }

    // Show the account container.
    state.topContainer.style.display = 'block';

    // Set batch update values.
    state.batchCurrentIdx = parseInt(state.batchCurrentIdx);
    state.batchTotalImages = parseInt(state.batchTotalImages);

    // Set the user's plan as well as their monthly image credits.
    if (state.yourPlanElement && state.maxMonthlyElement) {
        state.yourPlanElement.innerText = accountData.account_type;
        state.minMonthlyElement.innerText = accountData.monthly_image_count;
        state.maxMonthlyElement.innerText = accountData.monthly_image_limit;
        state.monthlyPercentageElement.style.width = (accountData.monthly_image_count / accountData.monthly_image_limit) * 100 + '%';
    }

    // Set the user's a la carte credits.
    if (state.maxBulkElement) {
        state.maxBulkElement.innerText = accountData.image_credit_limit;
        state.bulkPercentageElement.style.width = (accountData.image_credit_count / accountData.image_credit_limit) * 100 + '%';
    }
    state.minBulkElement.innerText = (accountData.image_credit_limit - accountData.image_credit_count) + (accountData.monthly_image_limit - accountData.monthly_image_count);

    // Set total images display.
    if (accountData.total_images) {
        let totalImages = parseInt(accountData.total_images);
        // Make sure that totalImages is not NaN.
        if (totalImages !== NaN) {
            state.totalImages.innerText = accountData.total_images;
        } else {
            state.totalImages.innerText = '0';
        }
    }

    // If there is a timestamp for the last batch update, display it.
    if (accountData.batch_timestamp) {
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        let batchTimestamp = new Date(accountData.batch_timestamp);
        let batchTimestampStr = batchTimestamp.toLocaleString();
        if (batchTimestamp.getMonth() >= 0 && batchTimestamp.getMonth() <= monthNames.length - 1) {
            batchTimestampStr = monthNames[batchTimestamp.getMonth()] + ' ' + batchTimestamp.getDate() + ', ' + batchTimestamp.getFullYear();
        }
        state.batchTimestamp.innerText = ' - ' + batchTimestampStr;
    }

    // Display recent batch alt text updates.
    let suggestionsHTML = '';
    for (let suggestion of accountData.bulk_suggestions) {
        if (suggestion.hasOwnProperty('suggestion') &&
            suggestion.suggestion) {
            let url = window.location.href.split('/').slice(0, -1).join('/') + `/post.php?post=${suggestion.post_ID}&action=edit`;
            suggestionsHTML += `<li>Title: <a href="${url}" target="_blank">${suggestion.title}</a> | New Alt Text: ${suggestion.suggestion} </li>`;
        }
    }
    state.finishedList.innerHTML = suggestionsHTML;

    if (hideBulkSuggestions || accountData.bulk_suggestions.length === 0) {
        state.updateList.classList.add('display-none');
    } else {
        state.updateList.classList.remove('display-none');
    }

    // Show top level container after everything else is displayed.
    showElement(state.topContainer);
}

/**
 * Cancels the batch display and updates the ui.
 * 
 * @param {Object}  state
 * @return {void}
 */
function stopBatchDisplay(state) {
    state.batchCurrentIdx = 0;
    state.batchInProgress = false;
    state.libraryUpdateStartButton.disabled = false;
    updateDisplay(state, false, false, false);
}

/**
* DOMContentLoaded callback function.
* 
* @param {Event} e Event object.
* @return {void}
*/
document.addEventListener('DOMContentLoaded', async (e) => {
    // State for the Library Updater page.
    let state = {
        topContainer: document.getElementById('load-account'),
        libraryUpdateStartButton: document.getElementById('libraryUpdateStartButton'),
        batchNotRunning: document.getElementById('altTextMagicBatchNotRunning'),
        batchRunning: document.getElementById('altTextMagicBatchRunning'),
        batchProgressText: document.getElementById('altTextMagicProgressText'),
        batchRemainingTime: document.getElementById('altTextMagicBatchRemainingTime'),
        cancelBatchButton: document.getElementById('altTextMagicCancelBatchButton'),
        batchProgressBar: document.getElementById('altTextMagicProgressBar'),
        altTextMagicAddAltTagsForm: document.getElementById('altTextMagicAddAltTagsForm'),
        minBulkElement: document.getElementById('bulkCredits'),
        totalImages: document.getElementById('totalImages'),
        missingImages: document.getElementById('missingImages'),
        finishedList: document.getElementById('finishedList'),
        batchTimestamp: document.getElementById('altTextMagicBatchTimestamp'),
        updateList: document.getElementById('updateList'),
        cancelledBatches: {},
        // Incremented by 1 for each batch.
        batchPrimaryKey: -1,
        currentBatchPrimaryKey: '-1',
        batchCancelled: false,
        batchInProgress: false,
        batchCurrentIdx: 0,
        batchTotalImages: 0,
        imagesMissingAltText: 0,
        chunkSize: 4,
        batchTimes: [],
    };

    // POST request to get the account data.
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

    // If the API Key is set, get the user's information.
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
        if (!responseOK) {
            // The request failed, redirect to the plugin page.
            let reloadURL = window.location.origin + window.location.pathname;
            window.location.href = reloadURL + '?page=alt-text-magic-plugin';
            return;
        }

        // Initial update display.
        updateDisplay(state, true, false, false);

        window.onbeforeunload = function () {
            if (state.batchInProgress) {
                return 'Leaving the page will cancel the library update, are you sure you want to leave?';
            }
            return null;
        }

        /**
         * Event handler for the batch update form submit event.
         * 
         * Starts a batch update that adds alt text to either all images
         * if the overwrite checkbox is checked, or just images missing
         * alt text if the overwrite checkbox is not checked.
         * 
         * @param {SubmitEvent} e Event object.
         * @return {void}
         */
        state.altTextMagicAddAltTagsForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            state.batchPrimaryKey++;
            let localBatchPrimaryKey = '' + state.batchPrimaryKey;
            state.currentBatchPrimaryKey = localBatchPrimaryKey;
            state.cancelledBatches[state.currentBatchPrimaryKey] = false;
            state.batchCancelled = false;
            state.batchInProgress = true;
            state.batchCurrentIdx = 0;
            state.batchTotalImages = 0;
            state.batchTimes = [];
            state.libraryUpdateStartButton.disabled = true;
            state.batchProgressBar.style.width = '0.5%';
            state.updateList.classList.add('display-none');

            updateDisplay(state, false, true, true);

            // Get the value of the overwrite alt text checkbox.
            let overwriteAltTags = jQuery("#altTextMagicOverwriteAltTagsCheckbox").is(":checked");

            let imagePosts = await Promise.resolve(jQuery.post(
                ajaxurl, {
                action: "alt_text_magic_get_image_posts",
                overwrite_alt_tags: overwriteAltTags,
                nonce: alt_text_magic_nonce_obj.get_image_posts_nonce,
            }));

            let imagePostsData = JSON.parse(imagePosts);

            let retries = 0;
            let maxRetries = 5;
            let backoffStart = 1000;
            let backoff = backoffStart;
            imagePostsData.monthly_image_limit = parseInt(imagePostsData.monthly_image_limit, 10);
            imagePostsData.monthly_image_count = parseInt(imagePostsData.monthly_image_count, 10);
            imagePostsData.image_credit_limit = parseInt(imagePostsData.image_credit_limit, 10);
            imagePostsData.image_credit_count = parseInt(imagePostsData.image_credit_count, 10);
            let available_monthly_images = imagePostsData.monthly_image_limit - imagePostsData.monthly_image_count
            let available_image_credits = imagePostsData.image_credit_limit - imagePostsData.image_credit_count
            let total_available_images = available_monthly_images + available_image_credits

            state.batchTotalImages = imagePostsData.posts_that_are_images.length;

            let notEnoughCredits = false;
            if (total_available_images < imagePostsData.posts_that_are_images.length) {
                notEnoughCredits = true;
                imagePostsData.posts_that_are_images = imagePostsData.posts_that_are_images.slice(0, total_available_images)
            }

            state.imagesMissingAltText = imagePostsData.total_missing_alt_text;
            updateDisplay(state, false, true, false);

            for (let i = 0; i < imagePostsData.posts_that_are_images.length; i += state.chunkSize) {
                // This batch has been cancelled, stop processing.
                if (state.cancelledBatches[localBatchPrimaryKey]) {
                    return;
                }
                const startDate = new Date();
                const chunk = imagePostsData.posts_that_are_images.slice(i, i + state.chunkSize);
                state.batchCurrentIdx = i;
                let didError = false;

                try {
                    let chunkChangeAltText = await Promise.resolve(jQuery.post(
                        ajaxurl, {
                        action: "alt_text_magic_chunk_change_alt_text",
                        chunk: JSON.stringify(chunk),
                        nonce: alt_text_magic_nonce_obj.chunk_change_alt_text_nonce,
                    }));

                    /** 
                     * This batch has been cancelled, stop processing.
                     * Check here to avoid a batchCurrentIdx update of 
                     * this cancelled batch before the next iteration begins
                     * and the batch gets cancelled.
                     */
                    if (state.cancelledBatches[localBatchPrimaryKey]) {
                        return;
                    }

                    let chunkChangeAltTextData = JSON.parse(chunkChangeAltText);

                    if (!chunkChangeAltTextData.success) {
                        didError = true;

                        let errorMessage = '';
                        if (chunkChangeAltTextData.response.message === "insufficient image count or credits") {
                            errorMessage = 'You have run out of credits.';
                        } else if (chunkChangeAltTextData.response.message === "invalid API key") {
                            errorMessage = 'Your API key is invalid.';
                        }

                        if (errorMessage !== '') {
                            toastyError(errorMessage);
                            stopBatchDisplay(state);
                            return;
                        }
                    } else {
                        let endDate = new Date();
                        let timeDiff = endDate - startDate;
                        state.batchTimes.push(timeDiff / 1000);
                        retries = 0;
                        backoff = backoffStart;

                        // php json encodes empty arrays as empty lists.
                        if (!Array.isArray(imagePostsData.posts_missing_alt_text)) {
                            // Remove and decrement any postIDs that were missing alt text.
                            for (let j = 0; j < chunk.length; j++) {
                                let postId = chunk[j];
                                if (imagePostsData.posts_missing_alt_text.hasOwnProperty(postId)) {
                                    delete imagePostsData.posts_missing_alt_text[postId];
                                    state.imagesMissingAltText--;
                                    if (state.imagesMissingAltText < 0) {
                                        state.imagesMissingAltText = 0;
                                    }
                                }
                            }
                        }
                    }
                } catch (error) {
                    didError = true;
                }

                if (didError) {
                    // Try again.
                    i -= state.chunkSize;
                    retries++;
                    // Exponential backoff.
                    await new Promise(resolve => setTimeout(resolve, backoff));
                    backoff *= 2;
                    if (retries >= maxRetries) {
                        // Stop trying.
                        toastyError('An error occurred, please try again.');
                        stopBatchDisplay(state);
                        return;
                    }
                } else {
                    /**
                     * Update batch current idx so that the updateDisplay
                     * below shows correctly, don't want to have to wait
                     * for the next updateDisplayCall in the next iteration
                     * for batchCurrentIdx to be updated.
                     */
                    state.batchCurrentIdx = i + state.chunkSize;
                }

                updateDisplay(state, false, false, false);
            }

            // Batch is complete.
            if (notEnoughCredits) {
                toastyError('You have run out of credits.');
            } else {
                toastySuccess('Library Updater completed successfully.');
            }
            stopBatchDisplay(state);
        });

        /**
         * Event handler for the cancel batch update button click event.
         * 
         * Cancels the batch update.
         * 
         * @param {MouseEvent} e Event object.
         * @return {void}
         */
        state.cancelBatchButton.addEventListener('click', async (e) => {
            e.preventDefault();
            if (state.cancelledBatches.hasOwnProperty(state.currentBatchPrimaryKey)) {
                state.cancelledBatches[state.currentBatchPrimaryKey] = true;
            }
            state.batchCancelled = true;
            state.batchInProgress = false;
            state.batchCurrentIdx = 0;

            state.libraryUpdateStartButton.disabled = false;

            updateDisplay(state, false, false, false);
        });

        // Accordion for recent update.
        let acc = document.getElementsByClassName("accordion");
        let i;

        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener("click", function () {
                this.classList.toggle("active");
                let panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + "px";
                }
            });
        }
    }

});