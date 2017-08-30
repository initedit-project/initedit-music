<div class="pageContainer defaultPageContainer">
    <div class="page">
        <div class="uploadContainer">
            <div class="titleContainer">
                <button class="siteButton" style="background-image: url(/public/images/website-icon.svg)"></button>
            </div>
            <div id="musicUploadStepOne">
                <div class="uploadMusicFileInputContainer" id="musicUploadCoverInputContainer_{{tmpid}}">
                    <div class="text">
                        Select Music File <br> or <br> Drop Here
                    </div>
                    <input accept=".mp3" 
                           type="file" 
                           id="uploadMusicFileInput" 
                           class="uploadMusicFileInput"
                           onchange="IniteditMusic.upload.music.onChangeMusicInput()"
                           multiple="true"/>
                </div>
            </div>
            <div id="musicUploadStepTwo">
                <div id="musicUploadStepTwoMusicList"></div>
            </div>
            <div id="musicUploadContainer" class="none">

                <div class="musicUploadSingleUIContainer" id="musicUploadSingleUIContainer_{{tmpid}}">
                    <div id="requestCancleContainer_{{tmpid}}" class="requestCancleContainer">
                        <div><h2>Are You Sure?</h2></div>
                        <div>
                            <button class="cancelRequestButton" onclick="IniteditMusic.upload.music.cancelSingalMusicUpload({{tmpid}})">YES</button>
                            <button class="cancelRequestButton" onclick="IniteditMusic.upload.music.requestcancelSingalMusicUploadHide({{tmpid}})">NO</button>
                        </div>
                    </div>
                    <div class="uploadProgress" id="uploadProgress_{{tmpid}}"></div>
                    <div class="musicUploadError" id="musicUploadErrorContainer_{{tmpid}}">
                        <span id="musicUploadError_{{tmpid}}"></span>
                        <button class="right button" onclick="IniteditMusic.upload.music.hideError({{tmpid}})">Close</button>
                    </div>
                    <div class="title" id="uploadDisplayTitle_{{tmpid}}">
                        <div >{{title}} 
                            <button class="right none" onclick="IniteditMusic.upload.music.removeSingleUpload({{tmpid}})" id="uploadRemoveSingle_{{tmpid}}" >Remove</button>
                            <button class="right button" onclick="IniteditMusic.upload.music.toggleDetail('{{tmpid}}')">Detail</button>
                            <button class="right none" id="uploadSaveProgress_{{tmpid}}"></button>
                        </div>

                    </div>

                    <div class="detail" id="musicDetail_{{tmpid}}">
                        <div class="detailLeft">
                            <div class="musicUploadCover">
                                <img src="/public/images/defaultMusicIcon.jpg" class="uploadPreviewImage" id="selectedImageUploadImg_{{tmpid}}" alt="Select Your Image"/>
                                <div class="musicUploadCoverInputContainer">
                                    <div class="text">Select Image</div>
                                    <input type="file" accept="image/*" id="selectedImageUploadInput_{{tmpid}}" onchange="IniteditMusic.upload.music.onChangeSelectedImage({{tmpid}})" class="musicUploadCoverInput"/>
                                </div>
                            </div>
                        </div>
                        <div class="detailRight">
                            <div><input class="title input" type="text" id="uploadTitle_{{tmpid}}" placeholder="Enter Title" value="{{title}}" /></div>
                            <div><input class="tags input" type="text" id="uploadTag_{{tmpid}}" placeholder="Enter Tags"/></div>
                            <div>
                                <p>Music Will Be</p>
                                <div>
                                    <span>
                                        <input type="radio" id="uploadPrivatePrivacy_{{tmpid}}"  name="privacy_{{tmpid}}" value="1">Private
                                    </span>
                                    <span>
                                        <input type="radio" id="uploadPrivacy_{{tmpid}}" name="privacy_{{tmpid}}" checked="checked" value="0">Public
                                    </span>
                                </div>
                            </div>
                            <div>
                                <textarea class="description input" id="uploadDesription_{{tmpid}}" placeholder="Enter Description"></textarea>
                            </div>

                            <div class="actionButtonContainer" id="uploadActionButtonContainer_{{tmpid}}">
                                <button class="button cta" onclick="IniteditMusic.upload.music.saveMusic({{tmpid}})">Save</button>
                                <button class="button" onclick="IniteditMusic.upload.music.requestCancelSingalMusicUpload({{tmpid}})">Cancel</button>
                            </div>
                        </div>
                    </div>
                    <div class="clearFix"></div>
                </div>
            </div>
        </div>
    </div>
</div>

