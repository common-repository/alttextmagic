function getQueryStringParam(name) {
    name = name.replace(/[[]/, '\\[').replace(/[\]]/, '\\]')
    let regex = new RegExp('[\\?&]' + name + '=([^&#]*)')
    let results = regex.exec(window.location.search)
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '))
}

async function updateAltText(altTextInput, postID) {
    let chunkChangeAltText = await Promise.resolve(jQuery.post(
        ajaxurl, {
        action: "alt_text_magic_chunk_change_alt_text",
        chunk: JSON.stringify([postID]),
        nonce: alt_text_magic_nonce_obj.chunk_change_alt_text_nonce,
    }));

    let chunkChangeAltTextData = JSON.parse(chunkChangeAltText);
    if (!chunkChangeAltTextData.success) {
        if (chunkChangeAltTextData.hasOwnProperty('response') &&
            chunkChangeAltTextData.response &&
            chunkChangeAltTextData.response.hasOwnProperty('message')) {
            if (chunkChangeAltTextData.response.message === "insufficient image count or credits") {
                toastyError('You have run out of credits.');
            } else if (chunkChangeAltTextData.response.message === "invalid API key") {
                toastyError('Your API key is invalid.');
            } else {
                toastyError('An error occurred, please try again.');
            }
        } else {
            toastyError('An error occurred, please try again.');
        }
    } else {
        if (altTextInput &&
            chunkChangeAltTextData.hasOwnProperty('response') &&
            chunkChangeAltTextData.response &&
            chunkChangeAltTextData.response.hasOwnProperty('captions') &&
            chunkChangeAltTextData.response.captions &&
            chunkChangeAltTextData.response.captions.length > 0) {
            altTextInput.value = chunkChangeAltTextData.response.captions[0];
            toastySuccess('Alt text updated successfully.');
        }
    }
}

function addUploadAltTextMagicButtonToModal(wpAltTextDescriptionId, postID, altTextMagicElementId) {
    let altTextDescription = document.getElementById(wpAltTextDescriptionId);
    // Remove any existing added elements. There should only be one at a time.
    let altTextContainer = document.getElementById(altTextMagicElementId);
    if (altTextContainer) {
        altTextContainer.remove();
    }

    if (altTextDescription) {
        let altTextMagicButton = createAltTextMagicButton(document.getElementById('attachment-details-two-column-alt-text'), postID, altTextMagicElementId);
        altTextDescription.appendChild(altTextMagicButton);
        return true;
    }
    return false;
}

function searchNodeListForElementId(nodeList, elementId) {
    let hasAltTextMagicElement = false;
    if (nodeList && nodeList.length > 0) {
        nodeList.forEach((node) => {
            if (hasAltTextMagicElement) {
                return;
            }
            if (node.id === elementId) {
                hasAltTextMagicElement = true;
            }
        });
    }
    return hasAltTextMagicElement;
}

function searchNodeListForElementClassName(nodeList, elementClassName) {
    let hasAltTextMagicElement = false;
    if (nodeList && nodeList.length > 0) {
        nodeList.forEach((node) => {
            if (hasAltTextMagicElement) {
                return;
            }
            if (node.classList && node.classList.contains(elementClassName)) {
                hasAltTextMagicElement = true;
            }
        });
    }
    return hasAltTextMagicElement;
}

function createAltTextMagicButton(altTextInput, postID, altTextMagicElementId) {
    let altTextMagicButton = document.createElement("button");
    altTextMagicButton.classList.add("alt-text-magic-nav-link", "alt-text-magic-outline-btn", "alt-text-magic-no-text-decoration", "alt-text-magic-media-change-alt-text-btn");
    altTextMagicButton.style.display = "inline-flex";
    altTextMagicButton.style.alignItems = "center";
    altTextMagicButton.style.marginTop = "8px";
    let altTextMagicIcon = document.createElement("img");
    altTextMagicIcon.src = alt_text_magic_resources.altTextMagicIconSrc;
    altTextMagicIcon.style.width = "16px";
    altTextMagicIcon.style.height = "16px";
    altTextMagicIcon.style.marginRight = "4px";
    let altTextMagicButtonText = document.createElement("span");
    altTextMagicButtonText.innerText = "Update Alt Text";
    altTextMagicButton.appendChild(altTextMagicIcon);
    altTextMagicButton.appendChild(altTextMagicButtonText);
    altTextMagicButton.onclick = async () => {
        if (altTextMagicButton) {
            altTextMagicButton.disabled = true;
        }
        if (altTextMagicButtonText) {
            altTextMagicButtonText.innerText = "Updating Alt Text...";
        }

        await updateAltText(altTextInput, postID);

        if (altTextMagicButton) {
            altTextMagicButton.disabled = false;
        }
        if (altTextMagicButtonText) {
            altTextMagicButtonText.innerText = "Update Alt Text";
        }
    };
    let div = document.createElement('div');
    div.id = altTextMagicElementId;
    div.appendChild(altTextMagicButton);
    return div;
}

/**
* DOMContentLoaded callback function.
* 
* @param {Event} e Event object.
* @return {void}
*/
document.addEventListener('DOMContentLoaded', async (e) => {
    let altTextMagicElementId = "alt-text-magic-alt-add-alt-text";
    let wpAltTextDescriptionId = "alt-text-description";

    if (window.location.href.includes('post.php')) {
        // The param on post.php is 'post', while on upload.php it is 'item'.
        let postID = getQueryStringParam('post');

        // No postID in the query string, return.
        if (!postID) {
            return;
        }

        postID = parseInt(postID, 10);

        // Could not be parsed into an integer, return.
        if (!postID) {
            return;
        }

        let altTextDescription = document.getElementById(wpAltTextDescriptionId);

        if (altTextDescription) {
            let altTextMagicButton = createAltTextMagicButton(document.getElementById('attachment_alt'), postID, altTextMagicElementId);
            altTextDescription.appendChild(altTextMagicButton);
        }
    } else if (window.location.href.includes('upload.php')) {
        let queryStringItem = getQueryStringParam('item');
        /**
        * The modal is open on page load as there is a query string param 'item'.
        * Add the button to the modal.
        */
        if (queryStringItem) {
            // Wait for the modal to be added to the DOM.
            let intervalCount = 0;
            let interval = setInterval(() => {
                intervalCount++;
                if (intervalCount > 20) {
                    clearInterval(interval);
                    return;
                }

                let postID = parseInt(queryStringItem, 10);
                // Could not be parsed into an integer, return.
                if (!postID) {
                    return;
                }
                let added = addUploadAltTextMagicButtonToModal(wpAltTextDescriptionId, postID, altTextMagicElementId);
                if (added) {
                    clearInterval(interval);
                }
            }, 500);
        }

        jQuery('body').on('click', '.left.dashicons', function () {
            let queryStringItem = getQueryStringParam('item');
            /**
            * The modal is open on page load as there is a query string param 'item'.
            * Add the button to the modal.
            */
            if (queryStringItem) {
                let postID = parseInt(queryStringItem, 10);
                // Could not be parsed into an integer, return.
                if (!postID) {
                    return;
                }

                addUploadAltTextMagicButtonToModal(wpAltTextDescriptionId, postID, altTextMagicElementId);
            }
        });

        jQuery('body').on('click', '.right.dashicons', function () {
            let queryStringItem = getQueryStringParam('item');
            /**
            * The modal is open on page load as there is a query string param 'item'.
            * Add the button to the modal.
            */
            if (queryStringItem) {
                let postID = parseInt(queryStringItem, 10);
                // Could not be parsed into an integer, return.
                if (!postID) {
                    return;
                }

                addUploadAltTextMagicButtonToModal(wpAltTextDescriptionId, postID, altTextMagicElementId);
            }
        });

        jQuery('body').on('click', 'ul.attachments li.attachment', function () {
            // Media li element click, which opens the modal. Add the button to the modal on click.
            let element = jQuery(this);
            // No data-id attribute, return.
            if (!element.attr('data-id')) {
                return;
            }
            let postID = element.attr('data-id');
            postID = parseInt(postID, 10);
            // Could not be parsed into an integer, return.
            if (!postID) {
                return;
            }
            addUploadAltTextMagicButtonToModal(wpAltTextDescriptionId, postID, altTextMagicElementId);
        });
    }
});
