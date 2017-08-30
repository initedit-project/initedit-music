<div class="pageContainer defaultPageContainer">
    <div class="page settingGeneral">
        <div>
            <h2>Site Name</h2>
            <input type="text" id="adminSiteName" placeholder="" value="<?php echo SITE_NAME; ?>"/>
            <button onclick="IniteditMusicAdmin.page.setting.general.changeTitle()">Save</button>    
        </div>
        <div>
            <h2>Help Email</h2>
            <input type="text" id="adminHelpEmail" placeholder="" value="<?php echo HELP_MAIL; ?>"/>
            <button onclick="IniteditMusicAdmin.page.setting.general.changeHelpEmail()">Save</button>    
        </div>
    </div>
</div>

