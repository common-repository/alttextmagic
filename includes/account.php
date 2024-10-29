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
        <div id="load-account" class="atm-main-container display-none">
            <div class="dash-header">
                <h1>Account</h1>
            </div>

            <div id="APIKeyInvalidContainer" class="activate-wrapper box row display-none">
                <div class="activate-header">Update your API Key</div>
                <div class="col-6">
                    <h3>Your API Key is invalid</h3>
                    <p>Did you just refresh your API Key? Update it below.</p>
                    <form id="altTextMagicInvalidAPIKeyForm" novalidate>
                        <div class="mb-3 mt-3">
                            <label for="altTextMagicInvalidAPIKeyInput" class="form-label">New API Key</label>
                            <input id="altTextMagicInvalidAPIKeyInput" class="form-control" name="changeAPIKey" placeholder="Enter new API key" required />
                            <div class="invalid-feedback">
                                Please enter an API key.
                            </div>
                        </div>
                        <div class="flex">
                            <button id="altTextMagicInvalidAPIKeyButton" type="submit" class="nav-link start-btn">
                                <span>Update</span>
                            </button>
                        </div>
                    </form>

                    <p class="mb-0 mt-4">Need to get a new API Key? Visit:</p>
                    <a href="https://user.alttextmagic.com/account" target="_blank">https://user.alttextmagic.com/account</a>
                </div>
            </div>

            <!-- Displayed if API Key IS NOT set -->
            <div id="altTextMagicContainer" class="row display-none">
                <div id="altTextMagicAPIKeyNotSetContainer">
                    <div class="activate-wrapper box row">

                        <div class="activate-header">Get Started</div>
                        <div class="col-6">
                            <h3>Step 1: Create your account</h3>
                            <p>Go to
                                <a href="https://user.alttextmagic.com/sign-up" target="_blank" rel="noopener">https://user.alttextmagic.com/sign-up</a>
                                to create your account.
                            </p>
                            <h3>Step 2: Add your API key</h3>
                            <form id="altTextMagicAPIKeyForm" novalidate>
                                <div class="mb-3">
                                    <label for="altTextMagicAPIKeyInput" class="form-label">API
                                        Key</label>
                                    <input id="altTextMagicAPIKeyInput" class="form-control" name="changeAPIKey" placeholder="API Key..." required />
                                    <div class="invalid-feedback">
                                        Please enter an API key.
                                    </div>
                                </div>

                                <button id="altTextMagicAPIKeyButton" type="submit" class="nav-link start-btn"> Add API Key </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Displayed if API Key IS set -->
            <div id="altTextMagicAPIKeySetContainer" class="row display-none">
                <div class="connected-wrapper box">
                    <div class="activate-header">API Key</div>
                    <div id="changeAPIKeyInitialContainer" class="col-1">
                        <h3>Your Account is Connected!</h3>
                        <p>You are on the <strong><span id="yourPlan" class="plan"></span></strong> plan.</p>
                        <p class="section-info">Need to upgrade? <a href="https://user.alttextmagic.com/manage-subscription" target="_blank">Manage your subscription.</a></p>
                        <button id="altTextMagicShowChangeAPIKeyButton" class="nav-link start-btn"> Change API Key</button>
                        <button id="altTextMagicDeleteAPIKeyButton" class="nav-link outline-btn inline-block no-text-decoration">Delete API Key</button>

                    </div>
                    <div id="changeAPIKeyChangeContainer" class="display-none col-1">
                        <h3>Change your API Key</h3>
                        <form id="altTextMagicChangeAPIKeyForm" novalidate>
                            <div class="mb-3 mt-3">
                                <label for="altTextMagicChangeAPIKeyInput" class="form-label">New API Key</label>
                                <input id="altTextMagicChangeAPIKeyInput" class="form-control" name="changeAPIKey" placeholder="Enter new API key" required />
                                <div class="invalid-feedback">
                                    Please enter an API key.
                                </div>
                            </div>
                            <div class="flex">
                                <button id="altTextMagicChangeAPIKeyCancelButton" type="button" class="nav-link outline-btn">
                                    <span>Cancel</span>
                                </button>
                                <button id="altTextMagicChangeAPIKeyButton" type="submit" class="nav-link start-btn">
                                    <span>Update</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Settings -->
            <div id="altTextMagicAccountSettings" class="row ">
                <div class="connected-wrapper box">
                    <div class="activate-header">Settings</div>
                    <div id="changeAPIKeyInitialContainer" class="col-1">
                        <h3>Generate Alt Text on Media Upload</h3>
                        <div class="form-check ml-0">
                            <input class="form-check-input" type="checkbox" value="" id="altTextMagicGenerateOnUpload" checked>
                            <label class="form-check-label font-14" for="altTextMagicGenerateOnUpload">
                                Alt text will automatically be generated for all new media uploads.
                            </label>
                        </div>
                        <h3>Alt Text Language</h3>
                        <p>Alt Text can be generated in 108 different languages. Changing the language will not update alt text already in your media library. You can run our Library Updater to overwrite existing alt text in the language you choose.</p>
                        <select id="languages-select" class="form-select" aria-label="Languages">
                            <option value="af">Afrikaans</option>
                            <option value="sq">Albanian</option>
                            <option value="am">Amharic</option>
                            <option value="ar">Arabic</option>
                            <option value="hy">Armenian</option>
                            <option value="az">Azerbaijani</option>
                            <option value="eu">Basque</option>
                            <option value="be">Belarusian</option>
                            <option value="bn">Bengali</option>
                            <option value="bs">Bosnian</option>
                            <option value="bg">Bulgarian</option>
                            <option value="ca">Catalan</option>
                            <option value="ceb">Cebuano</option>
                            <option value="zh-CN">Chinese (Simplified)</option>
                            <option value="zh-TW">Chinese (Traditional)</option>
                            <option value="co">Corsican</option>
                            <option value="hr">Croatian</option>
                            <option value="cs">Czech</option>
                            <option value="da">Danish</option>
                            <option value="nl">Dutch</option>
                            <option value="en-US" selected>English</option>
                            <option value="eo">Esperanto</option>
                            <option value="et">Estonian</option>
                            <option value="fi">Finnish</option>
                            <option value="fr">French</option>
                            <option value="fy">Frisian</option>
                            <option value="gl">Galician</option>
                            <option value="ka">Georgian</option>
                            <option value="de">German</option>
                            <option value="el">Greek</option>
                            <option value="gu">Gujarati</option>
                            <option value="ht">Haitian Creole</option>
                            <option value="ha">Hausa</option>
                            <option value="haw">Hawaiian</option>
                            <option value="he">Hebrew</option>
                            <option value="hi">Hindi</option>
                            <option value="hmn">Hmong</option>
                            <option value="hu">Hungarian</option>
                            <option value="is">Icelandic</option>
                            <option value="ig">Igbo</option>
                            <option value="id">Indonesian</option>
                            <option value="ga">Irish</option>
                            <option value="it">Italian</option>
                            <option value="ja">Japanese</option>
                            <option value="jv">Javanese</option>
                            <option value="kn">Kannada</option>
                            <option value="kk">Kazakh</option>
                            <option value="km">Khmer</option>
                            <option value="rw">Kinyarwanda</option>
                            <option value="ko">Korean</option>
                            <option value="ku">Kurdish</option>
                            <option value="ky">Kyrgyz</option>
                            <option value="lo">Lao</option>
                            <option value="la">Latin</option>
                            <option value="lv">Latvian</option>
                            <option value="lt">Lithuanian</option>
                            <option value="lb">Luxembourgish</option>
                            <option value="mk">Macedonian</option>
                            <option value="mg">Malagasy</option>
                            <option value="ms">Malay</option>
                            <option value="ml">Malayalam</option>
                            <option value="mt">Maltese</option>
                            <option value="mi">Maori</option>
                            <option value="mr">Marathi</option>
                            <option value="mn">Mongolian</option>
                            <option value="my">Myanmar (Burmese)</option>
                            <option value="ne">Nepali</option>
                            <option value="no">Norwegian</option>
                            <option value="ny">Nyanja (Chichewa)</option>
                            <option value="or">Odia (Oriya)</option>
                            <option value="ps">Pashto</option>
                            <option value="fa">Persian</option>
                            <option value="pl">Polish</option>
                            <option value="pt">Portuguese (Portugal, Brazil)</option>
                            <option value="pa">Punjabi</option>
                            <option value="ro">Romanian</option>
                            <option value="ru">Russian</option>
                            <option value="sm">Samoan</option>
                            <option value="gd">Scots Gaelic</option>
                            <option value="sr">Serbian</option>
                            <option value="st">Sesotho</option>
                            <option value="sn">Shona</option>
                            <option value="sd">Sindhi</option>
                            <option value="si">Sinhala (Sinhalese)</option>
                            <option value="sk">Slovak</option>
                            <option value="sl">Slovenian</option>
                            <option value="so">Somali</option>
                            <option value="es">Spanish</option>
                            <option value="su">Sundanese</option>
                            <option value="sw">Swahili</option>
                            <option value="sv">Swedish</option>
                            <option value="tl">Tagalog (Filipino)</option>
                            <option value="tg">Tajik</option>
                            <option value="ta">Tamil</option>
                            <option value="tt">Tatar</option>
                            <option value="te">Telugu</option>
                            <option value="th">Thai</option>
                            <option value="tr">Turkish</option>
                            <option value="tk">Turkmen</option>
                            <option value="uk">Ukrainian</option>
                            <option value="ur">Urdu</option>
                            <option value="ug">Uyghur</option>
                            <option value="uz">Uzbek</option>
                            <option value="vi">Vietnamese</option>
                            <option value="cy">Welsh</option>
                            <option value="xh">Xhosa</option>
                            <option value="yi">Yiddish</option>
                            <option value="yo">Yoruba</option>
                            <option value="zu">Zulu</option>
                        </select>
                        <p>
                            <button id="altTextMagicChangeSettingsButton" type="submit" class="nav-link start-btn">
                                <span>Save</span>
                            </button>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>