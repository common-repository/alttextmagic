<div id="atm-admin-page">
    <header class="atm-header">
        <div class="atm-container">
            <div class="atm-logo-area">
                <a href="https://www.alttextmagic.com/?utm_source=wpdashboard" target="_blank" rel="noopener"><img width="150" src="<?php echo plugin_dir_url(__DIR__); ?>assets/Alt-Text-Magic-logo.png" /></a>
                <h1 class="srs-only">Alt Text Magic</h1>
            </div>
        </div>
    </header>
    <main class="atm-admin-dashboard">
        <div id="load-account" class="atm-wide-container" style="display: none;">
            <div class="dash-header">
                <h1>Library Updater</h1>
            </div>
            <div class="row">
                <div class="batch-wrapper box">
                    <div class="row">
                        <div class="col-6">
                            <h3>Generate alternative text suggestions for multiple images.</h3>
                            <form method="post" id="altTextMagicAddAltTagsForm">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" value="missing-only" id="altTextMagicOnlyMissingAltTags" name="bulk-option" checked>
                                    <label class="form-check-label" for="altTextMagicOnlyMissingAltTags">
                                        Only process images with missing alternative text.
                                    </label><br>
                                    <input class="form-check-input" type="radio" value="overwrite-all" id="altTextMagicOverwriteAltTagsCheckbox" name="bulk-option">
                                    <label class="form-check-label" for="altTextMagicOverwriteAltTagsCheckbox">
                                        Overwrite all existing alternative text for images in media library with new suggestions (this cannot be undone)
                                    </label>
                                </div>
                                <div>
                                    <button type="submit" class="nav-link start-btn wide" id="libraryUpdateStartButton">
                                        Start Update
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="col-6">
                            <h3>Don't worry if you run out of credits. You can always buy more!</h3>
                            <table id="totals-box" class="library-totals">
                                <tbody>
                                    <tr>
                                        <td>
                                            <h4>Total Images in Media</h4>
                                            <p id="totalImages" class="lgnum">0</p>
                                        </td>
                                        <td>
                                            <h4>Images Missing Alt Text</h4>
                                            <p id="missingImages" class="lgnum">0</p>
                                        </td>
                                        <td>
                                            <h4>Generations Available</h4>
                                            <p id="bulkCredits" class="lgnum">0</p>
                                            <p>Monthly + Credits</p>
                                            <a id="buyMoreCredits" type="button" class="nav-link outline-btn inline-block no-text-decoration" href="https://user.alttextmagic.com/account" target="_blank">
                                                <span>Buy More Credits</span></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Displayed if a batch is running -->
                    <div id="altTextMagicBatchRunning" class="col-1">
                        <hr />
                        <h3>Update in progress (Please keep this page open until the update completes)</h3>
                        <div class="progress">
                            <div id="altTextMagicProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-info" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
                        </div>
                        <p id="altTextMagicProgressText">Batch is not running yet.</p>
                        <p id="altTextMagicBatchRemainingTime">Estimated time remaining:</p>
                        <button id="altTextMagicCancelBatchButton" class="nav-link outline-btn">Cancel</button>
                    </div>
                </div>
            </div>
            <div id="updateList" class="col-sm-6">
                <button class="accordion">Recent Library Update<span id="altTextMagicBatchTimestamp"></span></button>
                <div class="panel">
                    <div id="finished-list">
                        <ol id="finishedList">
                            <!-- List of finished suggestions will be inserted here -->

                            <li>Title: <a href="#">PIC30915-URE-Flowers-007_web</a> | Suggested Alt Text: Foust brick building in background with daffodils in foreground</li>

                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>