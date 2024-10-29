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
        <div id="load-account" class="atm-main-container" style="display: none;">
            <div class="dash-header">
                <h1>Dashboard</h1>
            </div>
            <!-- Display Number of Suggestions Generated -->
            <div class="row">
                <div class="connected-wrapper box">
                    <div class="activate-header">Alt text generations this cycle: <span id="subscriptionDates"></span></div>
                    <div class="row">
                        <div id="changeAPIKeyInitialContainer" class="col-6">
                            <p>You are on the <strong><span id="yourPlan" class="plan"></span></strong> plan with <strong><span id="maxMonthly2"></span></strong> alternative text generations per month.</p>
                            <div class="number-labels">
                                <div>
                                    <span id="minMonthly" class="min"></span>
                                </div>
                                <div>
                                    <span id="maxMonthly" class="max"></span>
                                </div>
                            </div>
                            <div class="use-bar">
                                <div class="progress">
                                    <div id="monthlyPercentage" class="progress-bar progress-bar-striped bg-warning" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <p><a type="button" class="nav-link outline-btn inline-block no-text-decoration" href="https://user.alttextmagic.com/manage-subscription" target="_blank"><span>Manage your subscription</span></a></p>
                            <p><a type="button" class="nav-link start-btn inline-block no-text-decoration" href="?page=alt-text-magic-bulk"><span>Run Library Updater</span></a></p>
                        </div>
                        <div class="col-6">
                            <table id="totals-box" class="dash-totals">
                                <tbody>
                                    <tr>
                                        <td class="dash-bank">
                                            <h4>Available Alt Text Credits</h4>
                                            <p id="bulkCredits" class="lgnum">0</p>
                                        </td>
                                        <td class="credit-text">
                                            <p>Alt Text Credits are purchased à la carte in increments of 50. Use these to supplement your monthly allowance without committing to a different subscription. These credits never expire and you can always buy more.</p>
                                            <a id="buyMoreCredits" type="button" class="nav-link outline-btn inline-block no-text-decoration" href="https://user.alttextmagic.com/account" target="_blank">
                                                <span>Buy More Credits</span></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>