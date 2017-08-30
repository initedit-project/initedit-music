var IniteditMusic = {
    recentObject: {
        recentAjax: null,
        response: null,
    },
    config: {
        showError: true,
        csrf: null,
        siteName: null,
        showSiteNameInTitle: true
    },
    extension: {
        isPage: function (page) {
            var current_page = window.location.pathname.split("/")[1];
            if (current_page == page) {
                return true;
            }
            return false;
        }
    },
    recentMusic: {
        music: new Array(),
        fullScreenMusic: null,
        searchMusic: new Array(),
        getById: function (id) {
            var musicCount = this.music.length;
            for (var i = 0; i < musicCount; i++)
            {
                var m = this.music[i];
                if (m.musicid == id) {
                    return m;
                }
            }
            var musicCount = this.searchMusic.length;
            for (var i = 0; i < musicCount; i++)
            {
                var m = this.searchMusic[i];
                if (m.musicid == id) {
                    return m;
                }
            }
            return false;
        },
        getByIdAny: function (id) {
            var musicCount = this.music.length;
            for (var i = 0; i < musicCount; i++)
            {
                var m = this.music[i];
                if (m.musicid == id) {
                    return m;
                }
            }
            if (this.fullScreenMusic != null) {
                if (this.fullScreenMusic.musicid == id) {
                    return this.fullScreenMusic;
                }
            }
            var musicCount = this.searchMusic.length;
            for (var i = 0; i < musicCount; i++)
            {
                var m = this.searchMusic[i];
                if (m.musicid == id) {
                    return m;
                }
            }

            var playlist = IniteditMusic.player.audio.playlist;
            if (playlist != null) {
                var musicCount = playlist.length;
                for (var i = 0; i < musicCount; i++)
                {
                    var m = playlist[i];
                    if (m.musicid == id) {
                        return m;
                    }
                }
            }


            return false;
        }

    },
    init: function () {

        IniteditMusic.config.csrf = $("body").attr("data-csrf");
        IniteditMusic.config.siteName = $("body").attr("data-site");
        IniteditMusic.storage.init();
        $("a").not(".linkDefault").click(IniteditMusic.helper.anchorClick);
        IniteditMusic.helper.reloadPage();
        IniteditMusic.notification.init();
        IniteditMusic.helper.init();
        IniteditMusic.player.init();
        window.onbeforeunload = IniteditMusic.page.beforeUnload;
        $(window).resize(function () {
            var w = $(window).width();
            if (w > 720) {
                IniteditMusic.player.updateBottomMusicProgressContainer();
            }
            if (w > 950) {

                IniteditMusic.player.waveform.regenerate($(".fullMusicDetailContainer .waveformContainer"));

            }
        });
        $(document).on("keydown", function (e) {
            if (e.keyCode == 32 && e.target == document.body) {
                e.preventDefault();
                IniteditMusic.player.toggle();
                if (IniteditMusic.player.audio.current != null) {


                }
            }

        });
    },
    helper: {
        init: function () {
            window.onpopstate = function (e) {
                IniteditMusic.helper.reloadPage();
            };
        },
        anchorClick: function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            IniteditMusic.helper.loadNewPage(href)
        },
        loadNewPage: function (url) {
            if (url[0] == "/")
            {
                url = url.substr(1);
            }
            if (url == "") {
                url = "home";
            }
            if (IniteditMusic.upload.music.uploadInfoAjax.isUploading())
            {
                IniteditMusic.upload.music.saveUploadState.init();
            }
            clearInterval(IniteditMusic.page.home.slider.config.sliderInterval);

            IniteditMusic.recentObject.recentAjax = $.ajax({
                type: "POST",
                url: "/ajax/" + url,
                data: {info: JSON.stringify({url: url})},
                dataType: "json",
                beforeSend: function (xhr) {
                    $("#mainProgressBar").show();
                },
                success: function (data) {
                    IniteditMusic.recentObject.response = data;
                    IniteditMusic.helper.processDownloadedPage(data, url);
                    $("#mainProgressBar").css("width", 100 + "%").delay(500).hide("fast", function () {
                        $("#mainProgressBar").css("width", 0 + "%")
                    });
//                     $("#mainProgressBar").hide();
                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                },
                xhr: function () {

                    // get the native XmlHttpRequest object
                    var xhr = $.ajaxSettings.xhr();

                    // set the onprogress event handler
                    if (xhr.upload) {
                        xhr.upload.onprogress = IniteditMusic.helper.updateProgress;
//                    xhr.onprogress = IniteditMusic.helper.updateProgress;
                        // set the onload event handler

                        xhr.onload = function () {

                            console.log('DONE!')
                        };
                    }
                    // return the customized object
                    return xhr;

                }
            });
        },
        reloadPage: function () {
            var href = window.location.pathname;
            IniteditMusic.helper.loadNewPage(href);
        },
        updateProgress: function (oEvent) {
            if (oEvent.lengthComputable) {
                var percentComplete = oEvent.loaded / oEvent.total * 100;

                $("#mainProgressBar").css("width", percentComplete * 0.67 + "%");
            } else {

                // Unable to compute progress information since the total size is unknown
            }
        },
        processDownloadedPage: function (data, url) {
            if (IniteditMusic.config.showSiteNameInTitle) {
                $("title").text(data.title + " - " + IniteditMusic.config.siteName);
            } else {
                $("title").text(data.title);
            }
            $("#main").html(data.data);
            $("#main a").not(".linkDefault").click(IniteditMusic.helper.anchorClick);
            IniteditMusic.helper.percessDownloadedMusicItem(data);
            if (url != "") {
                window.history.pushState("", "", "/" + url);
            } else {
                window.history.pushState("", "", "/");
            }
            if (IniteditMusic.upload.music.uploadInfoAjax.isUploading() && url == "upload")
            {
                IniteditMusic.upload.music.restoreUploadUI();
            }
            window.scrollTo(0, 0);
            IniteditMusic.helper.updateMetaTags(data);

        },
        percessDownloadedMusicItem: function (data, musicStore) {
            if (musicStore == undefined) {
                IniteditMusic.recentMusic.music = new Array();
                musicStore = IniteditMusic.recentMusic.music;
            }
            if (data.musicinfo != undefined)
            {
                var musicInfoCount = data.musicinfo.length;
                for (var j = 0; j < musicInfoCount; j++)
                {
                    var music = data.musicinfo[j].music;
                    var id = data.musicinfo[j].id;
                    var musicCount = music.length;
                    var type = data.musicinfo[j].display;
                    if (type == undefined || type == "box") {
                        var template = $("#musicItemContainer").prop('innerHTML');
                        for (var i = 0; i < musicCount; i++)
                        {
                            var musicInfo = music[i];
                            musicStore.push(musicInfo);
                            var txtTemplate = template;
                            txtTemplate = txtTemplate.replace(/\{\{musicid\}\}/g, musicInfo.musicid);
                            txtTemplate = txtTemplate.replace(/\{\{title\}\}/g, musicInfo.title);
                            txtTemplate = txtTemplate.replace(/\{\{username\}\}/g, musicInfo.user.name);
                            txtTemplate = txtTemplate.replace(/\{\{musicurl\}\}/g, musicInfo.musicurl);
                            (function () {
                                var musicInfo = music[i];
                                var $template = $(txtTemplate);
                                var $img = $("<img />").attr("src", musicInfo.image.thumb).on("load", function () {
                                    $img.remove();
                                    $img = null;
                                    $template.find(".backgroundBackgroundImage").css("background-image", "url(" + musicInfo.image.thumb + ")");
                                    $template.find(".backgroundBackgroundImage").fadeIn();
                                });
                                $("#" + id).append($template);
                            })();

                        }
                    } else if (type == "list") {
                        var template = $("#musicListItemContainer").prop('innerHTML');
                        for (var i = 0; i < musicCount; i++)
                        {
                            var musicInfo = music[i];
                            musicStore.push(musicInfo);
                            var txtTemplate = template;
                            txtTemplate = txtTemplate.replace(/\{\{musicid\}\}/g, musicInfo.musicid);
                            txtTemplate = txtTemplate.replace(/\{\{title\}\}/g, musicInfo.title);
                            txtTemplate = txtTemplate.replace(/\{\{username\}\}/g, musicInfo.user.name);
                            txtTemplate = txtTemplate.replace(/\{\{musicurl\}\}/g, musicInfo.musicurl);
                            (function () {
                                var musicInfo = music[i];
                                var $template = $(txtTemplate);
                                var $img = $("<img />").attr("src", musicInfo.image.thumb).on("load", function () {
                                    $img.remove();
                                    $img = null;
                                    $template.find(".backgroundBackgroundImage").css("background-image", "url(" + musicInfo.image.thumb + ")");
                                    $template.find(".backgroundBackgroundImage").fadeIn();
                                });
                                $("#" + id).append($template);
                                IniteditMusic.waveform.add($(".musicListItem_" + musicInfo.musicid).find(".waveform"), musicInfo.musicid);
                            })();
                        }
                    } else if (type == "playlist") {
                        var template = $("#musicPlaylistItemContainer").prop('innerHTML');
                        var $playlistItemList = null;
                        for (var i = 0; i < 1; i++)
                        {
                            var musicInfo = music[i];
                            musicStore.push(musicInfo);
                            var txtTemplate = template;
                            txtTemplate = txtTemplate.replace(/\{\{musicid\}\}/g, musicInfo.musicid);
                            txtTemplate = txtTemplate.replace(/\{\{title\}\}/g, musicInfo.title);
                            txtTemplate = txtTemplate.replace(/\{\{username\}\}/g, musicInfo.user.name);
                            txtTemplate = txtTemplate.replace(/\{\{musicurl\}\}/g, musicInfo.musicurl);
                            txtTemplate = txtTemplate.replace(/\{\{playlistid\}\}/g, id);
                            (function () {
                                var musicInfo = music[i];
                                var $template = $(txtTemplate);
                                var $img = $("<img />").attr("src", musicInfo.image.thumb).on("load", function () {
                                    $img.remove();
                                    $img = null;
                                    $template.find(".backgroundBackgroundImage").css("background-image", "url(" + musicInfo.image.thumb + ")");
                                    $template.find(".backgroundBackgroundImage").fadeIn();
                                });
                                $("#" + id).append($template);
                                $playlistItemList = $(".musicPlaylistItem_" + musicInfo.musicid).find(".playlistItemListContainer");
                                IniteditMusic.waveform.add($(".musicPlaylistItem_" + musicInfo.musicid).find(".waveform"), musicInfo.musicid);
                            })();
                        }
                        var template = $("#musicPlaylistItemListTemplate").prop('innerHTML');
                        for (var i = 0; i < musicCount; i++)
                        {
                            var musicInfo = music[i];
                            musicStore.push(musicInfo);
                            var txtTemplate = template;
                            txtTemplate = txtTemplate.replace(/\{\{musicid\}\}/g, musicInfo.musicid);
                            txtTemplate = txtTemplate.replace(/\{\{title\}\}/g, musicInfo.title);
                            txtTemplate = txtTemplate.replace(/\{\{username\}\}/g, musicInfo.user.name);
                            txtTemplate = txtTemplate.replace(/\{\{musicurl\}\}/g, musicInfo.musicurl);
                            txtTemplate = txtTemplate.replace(/\{\{playlistid\}\}/g, id);
                            txtTemplate = txtTemplate.replace(/\{\{number\}\}/g, (i+1));
                            (function () {
                                var musicInfo = music[i];
                                var $template = $(txtTemplate);
                                $playlistItemList.append($template);
                                $template = $playlistItemList.find(".playlistItemListContainer_" + musicInfo.musicid);
                                $template.data("playlist", music);
                                $template.data("playlistid", id);
                            })();
                        }
                    }

                    $("#main .stopPropagation").on("click", IniteditMusic.helper.stopPropagationAndPreventDefault);

                    $("#main #" + id + " a").not(".linkDefault").click(IniteditMusic.helper.anchorClick);



                }
            }
            if (IniteditMusic.player.audio.current != null) {
                var m = IniteditMusic.player.audio.current;
                $(".musicPlayPauseButtonItem_" + m.musicid).show();

                if (IniteditMusic.player.isPlaying()) {
                    $(".musicPlayPauseButtonItem_" + m.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.pause + ")");
                    $(".backgroundBackgroundImage_" + IniteditMusic.player.audio.current.musicid).addClass("circularRotate");
                } else {
                    $(".musicPlayPauseButtonItem_" + m.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                    $(".backgroundBackgroundImage_" + IniteditMusic.player.audio.current.musicid).removeClass("circularRotate");
                }
            }
            IniteditMusic.recentMusic.fullScreenMusic = null;
            if (data.isFullScreenMusic != undefined) {
                this.proccessDownloadedFullMusic(data);
            }

        },
        proccessDownloadedFullMusic: function (data) {
            IniteditMusic.log(data);
            IniteditMusic.player.waveform.generate($(".fullMusicDetailContainer .waveformContainer"), data.music);
            IniteditMusic.recentMusic.fullScreenMusic = data.music;
            $("#fullMusicPlayPauseButtonItem_" + data.music.musicid).show();

            if (IniteditMusic.player.isPlaying())
            {
                if (data.music.musicid == IniteditMusic.player.audio.current.musicid) {

                    $("#fullMusicPlayPauseButtonItem_" + data.music.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.pause + ")");
                } else {
                    $("#fullMusicPlayPauseButtonItem_" + data.music.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                }

            } else {
                $("#fullMusicPlayPauseButtonItem_" + data.music.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
            }
        },
        preventDefault: function (e) {
            e.preventDefault();
        },
        stopPropagationAndPreventDefault: function (e) {
            var evt = e ? e : window.event;
            if (evt.stopPropagation)
                evt.stopPropagation();
            if (evt.cancelBubble != null)
                evt.cancelBubble = true;
            e.preventDefault();
        },
        updateMetaTags: function (data) {
            IniteditMusic.log(data);

            $(".metaTitle").attr("content", data.title);
            $(".metaDescription").attr("content", data.description);
            $(".metaURL").attr("content", window.location.href);
            $(".metaURLCanonical").attr("href", window.location.href);
            if (data.isFullScreenMusic != undefined) {
                $(".metaImage").attr("content", data.music.image.original);
                $(".metaDescription").attr("content", data.music.description);
            } else {
                var bg = $(".siteButton").css("background-image");
                bg = bg.replace('url(', '').replace(')', '').replace(/\"/gi, "");
                $(".metaImage").attr("content", bg);
            }


        }
    },
    log: function (msg) {
        if (window.console)
            console.log(msg);
    },
    account: {
        signup: function () {
            var username = $("#signupName").val();
            var password = $("#signupPass").val();
            var confirmPassword = $("#signupPassConfirm").val();
            var email = $("#signupEmail").val();

            var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;


            if (!pattern.test(email)) {
                $("#wrongpassword").show();
                $("#wrongpassword").html("Invalid Email ID.");
                return;
            }


            if (username.length == 0) {
                $("#wrongpassword").show();
                $("#wrongpassword").html("User name is required.");
                return;
            }
            if (password.length == 0) {
                $("#wrongpassword").show();
                $("#wrongpassword").html("Password is required.");
                return;
            }
            if (confirmPassword.length == 0) {
                $("#wrongpassword").show();
                $("#wrongpassword").html("Confirm Password is required.");
                return;
            } else {
                if (password !== confirmPassword)
                {
                    $("#wrongpassword").show();
                    $("#wrongpassword").html("Password Didn't Match.");
                    return;
                }
            }
            $("#wrongpassword").hide();
            var info = {username: username, password: password, confirmPassword: confirmPassword, email: email};
            $.ajax({
                type: "POST",
                url: "/ajax/signup/create",
                data: {info: JSON.stringify(info)},
                dataType: "json",
                success: function (data) {
                    IniteditMusic.recentObject.response = data;
                    if (data.code == 1)
                    {
                        IniteditMusic.notification.toast.addMessageBox(0, data.message);
                        setTimeout(function () {
                            IniteditMusic.helper.loadNewPage(data.nextPage);
                        }, 2000);
                    } else {
                        $("#wrongpassword").show();
                        $("#wrongpassword").html(data.message);
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                }
            });
        }
        ,
        login: function () {
            var username = $("#loginName").val();
            var password = $("#loginPass").val();
            var rememberme = $('#logincheckbox').is(':checked');
            if (username.length == 0) {
                $("#wrongpassword").show();
                $("#wrongpassword").html("User name is required.");
                return;
            }
            if (password.length == 0) {
                $("#wrongpassword").show();
                $("#wrongpassword").html("Password is required.");
                return;
            }

            $("#wrongpassword").hide();
            var info = {username: username, password: password, rememberme: rememberme};
            $.ajax({
                type: "POST",
                url: "/ajax/login/validate",
                data: {info: JSON.stringify(info)},
                dataType: "json",
                success: function (data) {
                    IniteditMusic.recentObject.response = data;
                    if (data.code == 1)
                    {
                        IniteditMusic.notification.toast.addMessageBox(0, data.message);
                        $(".settingMenuLoggedIn").show();
                        $(".settingMenuLogIn").hide();
                        $("#settingMenuUserName").attr("href", data.profileurl);
                        setTimeout(function () {
                            IniteditMusic.helper.loadNewPage(data.nextPage);
                        }, 2000);
                    } else {
                        $("#wrongpassword").show();
                        $("#wrongpassword").html(data.message);
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                }
            });
        }
        ,
        logout: function (reload) {
            $.ajax({
                type: "POST",
                url: "/ajax/login/logout",
                data: {csrf: IniteditMusic.config.csrf},
                dataType: "json",
                success: function (data) {
                    IniteditMusic.recentObject.response = data;
                    if (data.code == 1)
                    {
                        IniteditMusic.notification.toast.addMessageBox(0, data.message);
                        $(".settingMenuLoggedIn").hide();
                        $(".settingMenuLogIn").show();
                        if (reload)
                        {
                            IniteditMusic.helper.reloadPage();
                        }
                    } else {
                        IniteditMusic.notification.toast.addMessageBox(1, data.message);
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                }
            });
        }
    },
    notification: {
        init: function () {
            this.toast.init();
        },
        toast: {
            init: function () {
                $("body").append("<div id='rightMessageBox' class='rightMessageBox'></div>");
            },
            addMessageBox: function (type, msg) {
                var id = "messageBox_" + (new Date()).getTime() + Math.ceil(Math.random() * 200);
                var boxMsg = '<div class="messageBox ' + ((type === 0) ? "messageBoxGreen" : "messageBoxRed") + '" id="' + id + '">\
                <ul class="hl">\
                    <li>' + msg + '</li>\
                    <li class="close" onclick="IniteditMusic.notification.toast.closeMessageBox(\'' + id + '\')">x</li>\
                </ul>\
            </div>';
                $("#rightMessageBox").prepend(boxMsg);
                var msgid = {boxid: id};
                var f = IniteditMusic.notification.toast.showMessageBox.bind(msgid);
                setTimeout(f, 0);
            },
            showMessageBox: function () {
                IniteditMusic.log(this);
                $("#" + this.boxid).css("opacity", "1");
                f = IniteditMusic.notification.toast.hideMessageBox.bind(this);
                setTimeout(f, 4000);
            },
            hideMessageBox: function () {
                $("#" + this.boxid).css("opacity", "0");
                f = IniteditMusic.notification.toast.removeMessageBox.bind(this);
                setTimeout(f, 500);
            },
            removeMessageBox: function () {
                $("#" + this.boxid).remove();
            },
            closeMessageBox: function (id) {
                var msgid = {boxid: id};
                f = IniteditMusic.notification.toast.hideMessageBox.bind(msgid);
                setTimeout(f, 0);
            }
        }
    },
    upload: {
        music: {
            save: function () {},
            config: {
                isUploading: false
            },
            onChangeMusicInput: function () {
                if (!window.File || !window.FileReader || !window.FileList || !window.Blob) {
                    //alert('The File APIs are not fully supported in this browser.');
                    alert('Featured required for this operation is not available.\nTry Updataing Browser.');
                    return;
                }
                var musicfiles = $("#uploadMusicFileInput").prop("files");
                var template = $("#musicUploadContainer").prop('innerHTML');
                $("#musicUploadStepTwoMusicList").html("");
                for (var i = 0; i < musicfiles.length; i++)
                {
                    var f = musicfiles[i];
                    var originalname = f.name;
                    var filesize = f.size;
                    var txtTemplate = template;
                    var tmpid = $("#musicUploadStepTwoMusicList > div").length;
                    var n = originalname.lastIndexOf(".");
                    var title = n > -1 ? originalname.substr(0, n) : originalname;
                    title = title.replace(/[^0-9a-z]/gi, ' ');
                    title = title.replace(/\s+/g, ' ');
                    txtTemplate = txtTemplate.replace(/\{\{title\}\}/g, title);
                    txtTemplate = txtTemplate.replace(/\{\{tmpid\}\}/g, tmpid);
                    $("#musicUploadStepTwoMusicList").append(txtTemplate);
                    var uploadInfo = {
                        extra: {
                            fileIndex: i,
                            tmpid: tmpid
                        },
                        file: f
                    }
//                    var uploadFunction = IniteditMusic.upload.music.uploadSingleFile.bind(uploadInfo);
//                    uploadFunction();
                    var uploadSingleFileAjax = IniteditMusic.upload.music.uploadSingleFile(uploadInfo);
                    uploadInfo.uploadSingleFileAjax = uploadSingleFileAjax;
                    var info = {
                        tmpid: tmpid,
                        uploadInfo: uploadInfo,
                        uploadSingleFileAjax: uploadSingleFileAjax,
                        saveMusicAjax: null
                    };
                    IniteditMusic.upload.music.uploadInfoAjax.ajaxObject.push(info);
                }
                if (musicfiles.length > 0)
                {
                    $("#musicUploadStepOne").hide();
                    $("#musicUploadStepTwo").show();
                    this.config.isUploading = true;
                }
                //Shows First Element Expanded
                $("#musicUploadStepTwoMusicList > .musicUploadSingleUIContainer:first-child .detail").slideDown()
            },
            toggleDetail: function (id) {
                $("#musicDetail_" + id).slideToggle();
            }
            ,
            hideError: function (id) {
                $("#musicUploadErrorContainer_" + id).slideUp();
            },
            showError: function (id, msg) {
                $("#musicUploadErrorContainer_" + id).slideDown();
                $("#musicUploadError_" + id).html(msg);
            },
            previewImageReadURL: function (input) {
                if (input.f && input.f[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#' + input.id).attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.f[0]);
                }
            },
            previewImageReadURLBackground: function (input) {
                if (input.f && input.f[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('#' + input.id).css('background-image', "url(" + e.target.result + ")");
                    }
                    reader.readAsDataURL(input.f[0]);
                }
            },
            onChangeSelectedImage: function (id) {
                var data = {
                    id: "selectedImageUploadImg_" + id,
                    f: $("#selectedImageUploadInput_" + id).get(0).files
                };
                IniteditMusic.upload.music.saveUploadState.updateStateAppendImageFile(id, $("#selectedImageUploadInput_" + id).get(0).files);
                IniteditMusic.upload.music.previewImageReadURL(data);
            },
            uploadInfoAjax: {
                ajaxObject: new Array(),
                appendSaveUploadAjax: function (id, saveMusicAjax) {
                    for (var i = 0; i < this.ajaxObject.length; i++)
                    {
                        var uploaded = this.ajaxObject[i];
                        if (uploaded.tmpid == id)
                        {
                            uploaded.saveMusicAjax = saveMusicAjax;
                        }
                    }
                },
                cancelSingleUpload: function (id) {
                    for (var i = 0; i < this.ajaxObject.length; i++)
                    {
                        var uploaded = this.ajaxObject[i];
                        if (uploaded.tmpid == id)
                        {
                            uploaded.uploadSingleFileAjax.abort();
                            if (uploaded.saveMusicAjax != null) {
                                uploaded.saveMusicAjax.abort();
                            }
                        }
                    }
                },
                isUploading: function () {
                    for (var i = 0; i < this.ajaxObject.length; i++)
                    {
                        var uploaded = this.ajaxObject[i];
                        if (uploaded.uploadSingleFileAjax != null) {
                            if (uploaded.uploadSingleFileAjax.readyState != 4)
                            {
                                return true;
                            }
                        }
                    }
                    if (IniteditMusic.upload.music.uploadedInfo.length > 0) {
                        return true;
                    }
                    return false;
                },
                isUploadingStarted: function () {
                    return this.ajaxObject.length == 0 ? false : true;
                },
                isUploadingCompleted: function () {
                    for (var i = 0; i < this.ajaxObject.length; i++)
                    {
                        var uploaded = this.ajaxObject[i];
                        if (uploaded.uploadSingleFileAjax != null) {
                            if (uploaded.uploadSingleFileAjax.readyState != 4)
                            {
                                return false;
                            }
                            if (uploaded.saveMusicAjax != null) {
                                if (uploaded.saveMusicAjax.readyState != 4)
                                {
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        }
                    }

                    return true;
                },
                removeSingleUploadAjax: function (id) {
                    for (var i = 0; i < this.ajaxObject.length; i++)
                    {
                        var uploaded = this.ajaxObject[i];
                        if (uploaded.tmpid == id)
                        {
                            this.ajaxObject.splice(i, 1);
                            return;
                        }
                    }
                }
            },
            uploadedInfo: new Array(),
            getUploadedInfo: function (id) {
                for (var i = 0; i < this.uploadedInfo.length; i++)
                {
                    var uploaded = this.uploadedInfo[i];
                    if (uploaded.extra.tmpid == id)
                    {
                        return uploaded;
                    }
                }
                return false;
            },
            removeUploadedInfo: function (id) {
                for (var i = 0; i < this.uploadedInfo.length; i++)
                {
                    var uploaded = this.uploadedInfo[i];
                    if (uploaded.extra.tmpid == id)
                    {
                        this.uploadedInfo.splice(i, 1);
                        return;
                    }
                }
                return false;
            },
            uploadSingleFile: function (uploadInfo) {
                var formData = new FormData();
                formData.append('extra', JSON.stringify(uploadInfo.extra));
                formData.append('music', uploadInfo.file);
                var uploadSingleFileAjax = $.ajax({
                    url: '/ajax/upload/draftmusic',
                    data: formData,
                    contentType: false,
                    processData: false,
                    type: "POST",
                    dataType: "json",
                    xhr: function () {
                        // get the native XmlHttpRequest object
                        var xhr = $.ajaxSettings.xhr();
                        // set the onprogress event handler
                        xhr.upload.onprogress = IniteditMusic.upload.music.updateSingalUploadProgress.bind(uploadInfo);
                        // set the onload event handler
                        xhr.upload.onload = function () {
                            console.log('DONE!')
                        };
                        return xhr;
                    },
                    success: function (data) {
                        IniteditMusic.upload.music.uploadSingleFileSuccess(data);
                    },
                    failure: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg + "</div>");
                        IniteditMusic.recentObject.response = errMsg;
                    },
                    error: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg.responseText + "</div>");
                        console.log(errMsg);
                    }
                });
                return uploadSingleFileAjax;
            }
            ,
            uploadSingleFileSuccess: function (data) {
                IniteditMusic.recentObject.response = data;
                if (data.code == 1) {
                    $("#uploadProgress_" + data.extra.tmpid).css("width", "100%");
                    IniteditMusic.upload.music.uploadedInfo.push(data);
                } else {
                    IniteditMusic.upload.music.showError(data.extra.tmpid, data.message);
                }
            },
            updateSingalUploadProgress: function (oEvent) {
                if (oEvent.lengthComputable) {
                    var percentComplete = oEvent.loaded / oEvent.total * 100;
                    $("#uploadProgress_" + this.extra.tmpid).css("width", percentComplete + "%");
//                    console.log(percentComplete);
                } else {
                    // Unable to compute progress information since the total size is unknown
                }
            },
            saveMusic: function (id) {
                var title = $("#uploadTitle_" + id).val();
                var tag = $("#uploadTag_" + id).val();
                var description = $("#uploadDesription_" + id).val();
                var imagefile = $("#selectedImageUploadInput_" + id).get(0);
                var privacy = $("#uploadPrivacy_" + id).is(":checked");
                var imageSelected = false;
                if (title == undefined || title == "")
                {
                    IniteditMusic.upload.music.showError(id, "Title is required.");
                    return;
                }
                if (tag == undefined || tag == "")
                {
                    IniteditMusic.upload.music.showError(id, "Tag is required.");
                    return;
                }
                if (description == "")
                {
//                    IniteditMusic.upload.music.showError(id, "Description is required.");
//                    return;
                }
                if ((imagefile.files && imagefile.files[0]))
                {
                    imageSelected = true;
                } else {
                    var s = IniteditMusic.upload.music.saveUploadState.getImageFile(id);
                    if (s != false) {
                        imageSelected = true;
                    }
                }



                var uploaded = this.getUploadedInfo(id);
                if (uploaded == false)
                {
                    //Add This upload to queue;
                    // and call  return
                    IniteditMusic.notification.toast.addMessageBox(1, "Wait! Music is not uploaded yet.");
                    return;
                }
                var uploadInfo = {
                    tmpid: id,
                    title: title,
                    tag: tag,
                    privacy: privacy,
                    description: description,
                    imageSelected: imageSelected,
                    uploaded: uploaded,
                    tmpnumber: uploaded.tmpnumber
                };
                var formData = new FormData();
                formData.append('extra', JSON.stringify(uploadInfo));
                if (imageSelected) {
                    if (imagefile.files && imagefile.files[0]) {
                        formData.append('image', imagefile.files[0]);
                    } else {
                        var s = IniteditMusic.upload.music.saveUploadState.getImageFile(id);
                        formData.append('image', s);
                    }
                }

                var saveMusicAjax = $.ajax({
                    url: '/ajax/upload/savemusic',
                    data: formData,
                    contentType: false,
                    processData: false,
                    type: "POST",
                    dataType: "json",
                    xhr: function () {
                        // get the native XmlHttpRequest object
                        var xhr = $.ajaxSettings.xhr();
                        // set the onprogress event handler
                        xhr.upload.onprogress = IniteditMusic.upload.music.updateSingalSaveProgress.bind(uploadInfo);
                        // set the onload event handler
                        xhr.upload.onload = IniteditMusic.upload.music.updateSingalSaveCompleted.bind(uploadInfo);
                        return xhr;
                    },
                    success: function (data) {
                        IniteditMusic.recentObject.response = data;
                        IniteditMusic.log(data);
                        IniteditMusic.upload.music.saveMusicSuccess(data);
                    },
                    failure: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg + "</div>");
                        IniteditMusic.recentObject.response = errMsg;
                    },
                    error: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg.responseText + "</div>");
                        console.log(errMsg);
                    }
                });
                IniteditMusic.upload.music.uploadInfoAjax.appendSaveUploadAjax(id, saveMusicAjax);
                IniteditMusic.upload.music.saveUploadState.updateSavedState(id, saveMusicAjax);
            },
            saveMusicSuccess: function (data) {
                if (data.code == 1) {
                    //IniteditMusic.upload.music.uploadedInfo.push(data);
                    var tmpid = data.extra.tmpid;
                    $("#uploadDisplayTitle_" + tmpid).addClass("savedTitle");
                    $("#uploadRemoveSingle_" + tmpid).show();
                    $("#uploadActionButtonContainer_" + tmpid).slideUp();
                    $("#musicUploadCoverInputContainer_" + tmpid).hide();
                    IniteditMusic.upload.music.hideError(tmpid);
                    if (data.messageShown == undefined) {
                        IniteditMusic.notification.toast.addMessageBox(0, data.message);
                        data.messageShown = true;
                    }
                } else {
                    $("#uploadSaveProgress_" + this.tmpid).hide();
                    IniteditMusic.upload.music.showError(data.extra.tmpid, data.message);
                }
                IniteditMusic.upload.music.config.isUploading = IniteditMusic.upload.music.uploadInfoAjax.isUploading()
            },
            updateSingalSaveProgress: function (oEvent) {
                if (oEvent.lengthComputable) {
                    $("#uploadSaveProgress_" + this.tmpid).show();
                    var percentComplete = oEvent.loaded / oEvent.total * 100;
                    $("#uploadSaveProgress_" + this.tmpid).html(" Saving - " + percentComplete + "% ");
                } else {
                    // Unable to compute progress information since the total size is unknown
                }
            },
            updateSingalSaveCompleted: function (oEvent) {
                $("#uploadSaveProgress_" + this.tmpid).show();
                $("#uploadSaveProgress_" + this.tmpid).html(" Saved ");
            },
            requestCancelSingalMusicUpload: function (tmpid) {
                $("#requestCancleContainer_" + tmpid).show();
            },
            requestcancelSingalMusicUploadHide: function (tmpid) {
                $("#requestCancleContainer_" + tmpid).hide();
            },
            cancelSingalMusicUpload: function (tmpid) {
                IniteditMusic.upload.music.uploadInfoAjax.cancelSingleUpload(tmpid);
                $("#requestCancleContainer_" + tmpid).fadeOut();
                $("#uploadRemoveSingle_" + tmpid).hide();
                $("#uploadActionButtonContainer_" + tmpid).slideUp();
                $("#uploadDisplayTitle_" + tmpid).addClass("errorTitle");
                $("#uploadProgress_" + tmpid).addClass("uploadProgressError");
                $("#uploadProgress_" + tmpid).css("width", "100%");
                setTimeout(function () {
                    IniteditMusic.upload.music.removeSingleUpload(tmpid);
                }, 2000);
            },
            saveUploadState: {
                info: new Array(),
                init: function () {
                    if (this.info == 0) {
                        this.saveState();
                    }
                },
                saveState: function () {
                    var totalObject = IniteditMusic.upload.music.uploadInfoAjax.ajaxObject.length;
                    for (var i = 0; i < totalObject; i++)
                    {

                        var data = {};
                        var uploadInfo = IniteditMusic.upload.music.uploadInfoAjax.ajaxObject[i].uploadInfo;
                        data.saveMusicAjax = IniteditMusic.upload.music.uploadInfoAjax.ajaxObject[i].saveMusicAjax;
                        data.uploadSingleFileAjax = IniteditMusic.upload.music.uploadInfoAjax.ajaxObject[i].uploadSingleFileAjax;
                        data.fileIndex = uploadInfo.extra.fileIndex;
                        data.tmpid = uploadInfo.extra.tmpid;
                        data.musicfile = uploadInfo.file;
                        data.title = $("#uploadTitle_" + data.tmpid).val();
                        data.tag = $("#uploadTag_" + data.tmpid).val();
                        data.description = $("#uploadDesription_" + data.tmpid).val();
                        data.privacy = $("#uploadPrivacy_" + data.tmpid).is(":checked");
                        data.isImageFileSelected = false;
                        var f = $("#selectedImageUploadInput_" + data.tmpid).prop("files");
                        if (f != undefined && f.length == 1)
                        {
                            data.isImageFileSelected = true;
                            data.imagefile = f[0];
                        } else {
                            data.isImageFileSelected = false;
                            data.imagefile = null;
                        }
                        this.info.push(data);
                    }
                },
                updateState: function () {
                    var totalObject = this.info.length;
                    for (var i = 0; i < totalObject; i++)
                    {
                        var data = this.info[i];
                        data.title = $("#uploadTitle_" + data.tmpid).val();
                        data.tag = $("#uploadTag_" + data.tmpid).val();
                        data.description = $("#uploadDesription_" + data.tmpid).val();
                        data.privacy = $("#uploadPrivacy_" + data.tmpid).is(":checked");
                        var f = $("#selectedImageUploadInput_" + data.tmpid).prop("files");
                        if (f != undefined && f.length == 1)
                        {
                            data.isImageFileSelected = true;
                            data.imagefile = f[0];
                        }
                    }
                },
                updateStateAppendImageFile: function (id, f) {
                    var totalObject = this.info.length;
                    for (var i = 0; i < totalObject; i++)
                    {
                        var data = this.info[i];
                        if (data.tmpid == i) {
                            if (f != undefined && f.length == 1)
                            {
                                data.isImageFileSelected = true;
                                data.imagefile = f[0];
                            }
                        }
                    }
                },
                getImageFile: function (id) {
                    var totalObject = this.info.length;
                    for (var i = 0; i < totalObject; i++)
                    {
                        var data = this.info[i];
                        if (data.tmpid == i) {
                            if (data.isImageFileSelected)
                                return data.imagefile;
                            else
                                return false;
                        }
                    }
                    return false;
                },
                updateSavedState: function (id, saveMusicAjax) {
                    var totalObject = this.info.length;
                    for (var i = 0; i < totalObject; i++)
                    {
                        var data = this.info[i];
                        if (data.tmpid == id) {

                            data.title = $("#uploadTitle_" + data.tmpid).val();
                            data.tag = $("#uploadTag_" + data.tmpid).val();
                            data.description = $("#uploadDesription_" + data.tmpid).val();
                            data.privacy = $("#uploadPrivacy_" + data.tmpid).is(":checked");
                            var f = $("#selectedImageUploadInput_" + data.tmpid).prop("files");
                            if (f != undefined && f.length == 1)
                            {
                                data.isImageFileSelected = true;
                                data.imagefile = f[0];
                            }
                            data.saveMusicAjax = saveMusicAjax;
                        }
                    }
                },
                removeSavedState: function (id) {
                    var totalObject = this.info.length;
                    for (var i = 0; i < totalObject; i++)
                    {
                        var data = this.info[i];
                        if (data.tmpid == id) {
                            this.info.splice(i, 1);
                        }
                    }
                }
            },
            restoreUploadUI: function () {
                var template = $("#musicUploadContainer").prop('innerHTML');
                var totalObject = this.saveUploadState.info.length;
                for (var i = 0; i < totalObject; i++)
                {
                    var data = this.saveUploadState.info[i];
                    var f = data.musicfile;
                    var txtTemplate = template;
                    var tmpid = data.tmpid;
                    var title = data.title;
                    title = title.replace(/[^0-9a-z]/gi, ' ');
                    title = title.replace(/\s+/g, ' ');
                    txtTemplate = txtTemplate.replace(/\{\{title\}\}/g, title);
                    txtTemplate = txtTemplate.replace(/\{\{tmpid\}\}/g, tmpid);
                    $("#musicUploadStepTwoMusicList").append(txtTemplate);
                    $("#uploadTag_" + tmpid).val(data.tag);
                    $("#uploadDesription_" + tmpid).val(data.description);
                    if (data.privacy) {

                    } else {
                        $("#uploadPrivatePrivacy_".tmpid).attr("checked", "checked");
                    }

                    var info = {
                        id: "selectedImageUploadImg_" + tmpid,
                        f: new Array(data.imagefile)
                    };
                    this.previewImageReadURL(info);
                    if (data.uploadSingleFileAjax.readyState == 4)
                    {
                        IniteditMusic.upload.music.uploadSingleFileSuccess(data.uploadSingleFileAjax.responseJSON);
                    }
                    if (data.saveMusicAjax != null) {
                        if (data.saveMusicAjax.readyState == 4)
                        {
                            IniteditMusic.upload.music.saveMusicSuccess(data.saveMusicAjax.responseJSON);
                        }
                    }
                }

                if (totalObject > 0)
                {
                    $("#musicUploadStepOne").hide();
                    $("#musicUploadStepTwo").show();
                    $("#musicUploadStepTwoMusicList > .musicUploadSingleUIContainer:first-child .detail").slideDown()
                }
            },
            removeSingleUpload: function (id) {
                $("#musicUploadSingleUIContainer_" + id).slideUp();
                this.uploadInfoAjax.removeSingleUploadAjax(id);
                this.saveUploadState.removeSavedState(id);
                this.removeUploadedInfo(id);
                if (!IniteditMusic.upload.music.uploadInfoAjax.isUploading())
                {
                    $("#musicUploadStepOne").show();
                    $("#musicUploadStepTwo").hide();
                }
                var totalObject = this.saveUploadState.info.length;
                if (totalObject > 0)
                {
                    $("#musicUploadStepOne").hide();
                    $("#musicUploadStepTwo").show();
                    $("#musicUploadStepTwoMusicList > .musicUploadSingleUIContainer:first-child .detail").slideDown()
                }



            },
            edit: {
                image: null,
                editAjax: null,
                onClickSaveEdit: function (id) {
                    var title = $("#editTitleInput").val();
                    var tag = $("#editTagInput").val();
                    var description = $("#editDescriptionInput").val();
                    var privacy = $("#editMusicPrivacy").is(":checked");
                    $("#editError").show();
                    if (title.length == 0)
                    {
                        $("#editError").html("Title is required");
                        return;
                    }
                    if (tag.length == 0)
                    {
                        $("#editError").html("Tag is required");
                        return;
                    }
                    $("#editError").hide();
                    var imageSelected = false;
                    if (IniteditMusic.upload.music.edit.image != null) {
                        imageSelected = true;
                    }

                    var uploadInfo = {
                        title: title,
                        tag: tag,
                        description: description,
                        privacy: privacy,
                        musicid: id,
                        imageSelected: imageSelected
                    };
                    var formData = new FormData();
                    formData.append('extra', JSON.stringify(uploadInfo));
                    if (imageSelected) {
                        formData.append('image', IniteditMusic.upload.music.edit.image);
                    }
                    IniteditMusic.upload.music.edit.editAjax = $.ajax({
                        url: '/ajax/upload/editmusic',
                        data: formData,
                        contentType: false,
                        processData: false,
                        type: "POST",
                        dataType: "json",
                        success: function (data) {
                            if (data.code == 1) {
                                IniteditMusic.notification.toast.addMessageBox(0, data.message);
                            } else {
                                IniteditMusic.notification.toast.addMessageBox(1, data.message);
                            }
                            IniteditMusic.log(data);
                        },
                        failure: function (errMsg) {
                            if (IniteditMusic.config.showError)
                                $("body").append("<div>" + errMsg + "</div>");
                            IniteditMusic.recentObject.response = errMsg;
                        },
                        error: function (errMsg) {
                            if (IniteditMusic.config.showError)
                                $("body").append("<div>" + errMsg.responseText + "</div>");
                            console.log(errMsg);
                        }
                    });


                },
                onChangeImageFile: function () {

                    var files = $("#editImageInput").prop("files");
                    if (files != undefined && files[0] != undefined) {

                        IniteditMusic.upload.music.edit.image = files[0];
                        var info = {
                            id: "editImageContainer",
                            f: files
                        }
                        IniteditMusic.upload.music.previewImageReadURLBackground(info);
                    }
                },
                onClickShowDeleteMenu: function () {
                    $("#confirmDeleteContainer").fadeIn();
                },
                onClickHideDeleteMenu: function () {
                    $("#confirmDeleteContainer").fadeOut();
                },
                onClickDeleteMusic: function (id) {
                    var info = {
                        musicid: id
                    };
                    $.ajax({
                        type: "POST",
                        url: '/ajax/upload/deletemusic',
//                        data: {info: JSON.stringify({extra: info})},
                        data: {extra: JSON.stringify(info)},
                        dataType: "json",
                        success: function (data) {
                            IniteditMusic.recentObject.response = data;
                            if (data.code == 1) {
                                IniteditMusic.notification.toast.addMessageBox(0, data.message);
                                if (data.nextpage != undefined)
                                {
                                    setTimeout(function () {
                                        IniteditMusic.helper.loadNewPage(data.nextpage);
                                    }, 1000);

                                }
                            } else {
                                IniteditMusic.notification.toast.addMessageBox(1, data.message);
                            }
                            IniteditMusic.log(data);
                        },
                        failure: function (errMsg) {
                            if (IniteditMusic.config.showError)
                                $("body").append("<div>" + errMsg + "</div>");
                            IniteditMusic.recentObject.response = errMsg;
                        },
                        error: function (errMsg) {
                            if (IniteditMusic.config.showError)
                                $("body").append("<div>" + errMsg.responseText + "</div>");
                            console.log(errMsg);
                        }
                    });
                }
            }
        }
    },
    player: {
        config: {
            volume: {
                init: 50,
                min: 0,
                max: 100
            },
            track: {
                startedSkiping: false
            },
            time: {forwardSkipSecond: 30,
                backwardSkipSecond: 10
            },
            icon: {
                play: "/public/images/play-icon.svg",
                pause: "/public/images/pause-icon.svg",
                loading: "/public/images/loader.gif",
                repeat: "/public/images/repeat-icon.svg",
                repeathighlight: "/public/images/repeat-icon-highlight.svg",
                volume: {
                    high: "/public/images/volume-high-icon.svg",
                    medium: "/public/images/volume-medium-icon.svg",
                    low: "/public/images/volume-low-icon.svg",
                    mute: "/public/images/mute-icon.svg"
                }
            },
            repeat: true
        },
        init: function () {

            /*
             * 
             * Call Two times to make original
             */
            IniteditMusic.player.config.repeat = IniteditMusic.storage.local.getRepeat();
            IniteditMusic.player.config.volume.init = IniteditMusic.storage.local.getVolume();
            this.updateRepeatUI();

            this.audio.init();
            this.sliders.init();

        },
        sliders: {
            init: function () {
                var val = IniteditMusic.player.config.volume.max - IniteditMusic.player.config.volume.init;
                $("#bottomMusicVolumeCircle").css("top", val);
                $("#bottomMusicVolumeProgress").css("height", val + "px");

                if (IniteditMusic.player.config.volume.init == 0) {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.mute + ")")
                } else if (IniteditMusic.player.config.volume.init < 30) {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.low + ")")
                } else if (IniteditMusic.player.config.volume.init < 70) {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.medium + ")")
                } else {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.high + ")")
                }

            },
            volume: function () {
                var val = $("#bottomMusicVolumeInput").val();

                var volumeInt = (IniteditMusic.player.config.volume.max - parseInt(val)) / IniteditMusic.player.config.volume.max;
                var val = parseInt(val);

//                console.log(val);
//                var volume = IniteditMusic.player.audio.audioObject.volume;
                IniteditMusic.player.audio.audioObject.volume = volumeInt;
                IniteditMusic.player.sliders.updateVolumeUI(volumeInt, val);

            },
            volumeDone: function () {
                var val = $("#bottomMusicVolumeInput").val();
                var volumeInt = (IniteditMusic.player.config.volume.max - parseInt(val)) / IniteditMusic.player.config.volume.max;
                IniteditMusic.player.audio.audioObject.volume = volumeInt;
                IniteditMusic.storage.local.setVolume(volumeInt);
                IniteditMusic.player.sliders.updateVolumeUI(volumeInt, val);

            },
            updateVolumeUI: function (volumeInt, val) {

                $("#bottomMusicVolumeCircle").css("top", val + "px");
                $("#bottomMusicVolumeProgress").css("height", val + "px");

                if (volumeInt == 0) {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.mute + ")")
                } else if (volumeInt * 100 < 30) {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.low + ")")
                } else if (volumeInt * 100 < 70) {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.medium + ")")
                } else {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.high + ")")
                }

            },
            track: function () {
                var val = $("#bottomMusicTrackInput").val();
                var val = parseInt(val);
                $("#bottomMusicTrackCircle").css("left", val + "%");
                $("#bottomMusicTrackProgress").css("width", val + "%");
                IniteditMusic.player.config.track.startedSkiping = true;
            },
            trackDone: function () {
                IniteditMusic.player.config.track.startedSkiping = false;
                var val = $("#bottomMusicTrackInput").val();
                var val = parseInt(val);
                IniteditMusic.player.audio.skipTimeBy(val);
            }
        },
        volume: {
            bottomToggle: function () {
                $("#bottomVolumeChangeContainer").slideToggle(200);
            },
            bottomHide: function () {
                $("#bottomVolumeChangeContainer").slideUp(200);
            },
            bottomShow: function () {
                $("#bottomVolumeChangeContainer").slideDown(200);
            },
            toggleMute: function () {
                IniteditMusic.player.audio.audioObject.muted = !IniteditMusic.player.audio.audioObject.muted;
                if (IniteditMusic.player.audio.audioObject.muted) {
                    $("#bottomVolumeButton").css("background-image", "url(" + IniteditMusic.player.config.icon.volume.mute + ")");
                } else {
                    IniteditMusic.player.sliders.volume();
                }
                IniteditMusic.player.volume.bottomToggle();

            }
        },
        play: function (musicObject) {
            this.audio.current = musicObject;
            if (IniteditMusic.player.isPlaying()) {
                this.audio.pause();
            }
            this.audio.play(musicObject);
            $("#bottomMusicTitle").html(musicObject.title);

            $("#bottomMusicImage").css("background-image", "url(" + musicObject.image.thumb + ")");
            $("#bottomPlayingMusicLink").prop("href", musicObject.musicurl);
            $("#bottomMusicUserName").html(musicObject.user.name);
            $("#bottomPlayPauseButton").css("background-image", "url(" + IniteditMusic.player.config.icon.loading + ")");
            $("#bottomPlayerContainer").slideDown("fast", function () {
                setTimeout(function () {
                    $("#footer").css("margin-bottom", $("#bottomPlayerContainer").outerHeight() + "px");
                }, 50);

            });

            if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                if (IniteditMusic.recentMusic.fullScreenMusic.musicid != musicObject.musicid) {
                    $("#fullMusicPlayPauseButtonItem_" + IniteditMusic.recentMusic.fullScreenMusic.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                }
            }

            $(".musicPlayPauseButtonItem_" + musicObject.musicid).show();
            this.nextAndPreviousButtonCheck();


        },
        nextAndPreviousButtonCheck: function () {


            if (this.audio.playlist != null) {

                var m = this.audio.current;
                var position = this.audio.playlist.indexOf(m);

                if (position == 0) {
                    $("#bottomPreviousButton").prop('disabled', true);
                } else {
                    $("#bottomPreviousButton").prop('disabled', false);
                }
                if (position == this.audio.playlist.length - 1)
                {
                    if (IniteditMusic.player.audio.isRepeat()) {
                        $("#bottomNextButton").prop('disabled', false);
                    } else {
                        $("#bottomNextButton").prop('disabled', true);
                    }

                } else {
                    $("#bottomNextButton").prop('disabled', false);
                }
            } else {
                $("#bottomPreviousButton").prop('disabled', true);
                $("#bottomNextButton").prop('disabled', true);
            }

        },
        pause: function () {
            this.audio.pause();
        },
        resume: function () {
            this.audio.resume();
        },
        toggle: function () {
            if (this.isPlaying())
            {
                this.audio.pause();
                $("#bottomPlayPauseButton").css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                $(".backgroundBackgroundImage_" + IniteditMusic.player.audio.current.musicid).removeClass("circularRotate");
                if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid == IniteditMusic.player.audio.current.musicid) {
                        $("#fullMusicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");

                    }
                }

            } else {
                this.audio.resume();
                $("#bottomPlayPauseButton").css("background-image", "url(" + IniteditMusic.player.config.icon.pause + ")");
                $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.pause + ")");
                $(".backgroundBackgroundImage_" + IniteditMusic.player.audio.current.musicid).addClass("circularRotate");
                if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid == IniteditMusic.player.audio.current.musicid) {
                        $("#fullMusicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.pause + ")");

                    }
                }

            }
        },
        isPlaying: function () {
            return !this.audio.audioObject.paused;
        },
        onClickAddToCurrentPlaylist: function (id) {
            var m = IniteditMusic.recentMusic.getByIdAny(id);
            if (IniteditMusic.player.audio.playlist != null) {
                var exists = IniteditMusic.player.audio.playlist.indexOf(m);
                if (exists >= 0) {

                } else {
                    IniteditMusic.player.audio.playlist.push(m);
                }
            } else if (IniteditMusic.player.audio.current == null) {
                if (IniteditMusic.player.audio.playlist == null)
                    IniteditMusic.player.audio.playlist = new Array();
                IniteditMusic.player.audio.playlist.push(m);
                IniteditMusic.player.play(m);
            } else if (IniteditMusic.player.audio.current == m) {
                if (IniteditMusic.player.audio.playlist == null)
                    IniteditMusic.player.audio.playlist = new Array();
                IniteditMusic.player.audio.playlist.push(m);
            } else {
                if (IniteditMusic.player.audio.playlist == null)
                    IniteditMusic.player.audio.playlist = new Array();
                IniteditMusic.player.audio.playlist.push(m);
            }
            var exists = IniteditMusic.player.audio.playlist.indexOf(m);
            if (exists >= 0) {
                IniteditMusic.notification.toast.addMessageBox(0, "Added.");
                IniteditMusic.player.updateBottomMusicProgressContainer();
            }
        },
        audio: {
            audioObject: new Audio(),
            current: null,
            playlist: null,
            init: function () {
                IniteditMusic.player.audio.audioObject.volume = IniteditMusic.player.config.volume.init / IniteditMusic.player.config.volume.max;
                this.audioObject.addEventListener("timeupdate", this.updateAudioTime, false);
                this.audioObject.addEventListener("playing", this.audioStartedPlaying, false);
                this.audioObject.addEventListener("pause", this.audioPausedPlaying, false);
                this.audioObject.addEventListener("ended", this.audioEnded, false);
                this.audioObject.addEventListener("waiting", this.audioWaitingPlaying, false);
                this.audioObject.addEventListener("stalled", this.audioStalledPlaying, false);
                this.audioObject.addEventListener("error", this.audioError, false);
            },
            play: function (musicObject) {
                if (IniteditMusic.player.isPlaying()) {
                    this.audioObject.pause();
                }


                this.audioObject.src = musicObject.track.original;
                this.audioObject.load(); //call this to just preload the audio without playing
                this.audioObject.oncanplaythrough = this.audioObject.play();

            },
            pause: function () {
                this.audioObject.pause();
            },
            resume: function () {
                this.audioObject.play();
            },
            skipTimeBy: function (percentage) {
                var calculate = (this.audioObject.duration / 100) * percentage;
                this.audioObject.currentTime = calculate;
            },
            updateAudioTime: function () {
                var currentTime, progress, totalDuration;
                progress = (IniteditMusic.player.audio.audioObject.currentTime / IniteditMusic.player.audio.audioObject.duration) * 100;
                if (!isNaN(IniteditMusic.player.audio.audioObject.currentTime)) {
                    currentTime = IniteditMusic.player.audio.secondsToHms(IniteditMusic.player.audio.audioObject.currentTime);
                } else {
                    currentTime = "0:00";
                }
                if (!isNaN(IniteditMusic.player.audio.audioObject.duration)) {
                    totalDuration = IniteditMusic.player.audio.secondsToHms(IniteditMusic.player.audio.audioObject.duration);
                } else {
                    totalDuration = "0:00";
                }
                $("#bottomPlayedTime").text(currentTime);
                $("#bottomTotalTime").text(totalDuration);
                if (!IniteditMusic.player.config.track.startedSkiping) {
                    $("#bottomMusicTrackProgress").css("width", progress + "%");
                    $("#bottomMusicTrackCircle").css("left", progress + "%");
                    var m = IniteditMusic.player.audio.current;

                    $(".updateWaveformPlayedCanvas_" + m.musicid).css("width", progress + "%");
                }


            },
            secondsToHms: function (d) {
                d = Number(d);
                var h = Math.floor(d / 3600);
                var m = Math.floor(d % 3600 / 60);
                var s = Math.floor(d % 3600 % 60);
                return ((h > 0 ? h + ":" + (m < 10 ? "0" : "") : "") + m + ":" + (s < 10 ? "0" : "") + s);
            },
            audioStartedPlaying: function () {
                $("#bottomPlayPauseButton").css("background-image", "url(" + IniteditMusic.player.config.icon.pause + ")");
                $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.pause + ")");
                if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid == IniteditMusic.player.audio.current.musicid) {
                        $("#fullMusicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.pause + ")");
                    }
                }
            },
            audioPausedPlaying: function () {
                IniteditMusic.log("Paused Called");
                $("#bottomPlayPauseButton").css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid == IniteditMusic.player.audio.current.musicid) {
                        $("#fullMusicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                    }
                }
            },
            audioEnded: function () {
                IniteditMusic.music.viewCount(IniteditMusic.player.audio.current);

                var m = IniteditMusic.player.audio.current;




                $("#bottomPlayPauseButton").css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                $(".backgroundBackgroundImage_" + IniteditMusic.player.audio.current.musicid).removeClass("circularRotate");
                if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid == IniteditMusic.player.audio.current.musicid) {
                        $("#fullMusicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                    }
                }
                IniteditMusic.player.next();


            },
            audioWaitingPlaying: function () {
                IniteditMusic.log("Waiting Called");
                $("#bottomPlayPauseButton").css("background-image", "url(" + IniteditMusic.player.config.icon.loading + ")");
                $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.loading + ")");
                if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid == IniteditMusic.player.audio.current.musicid) {
                        $("#fullMusicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.loading + ")");
                    }
                }
            },
            audioStalledPlaying: function () {
                IniteditMusic.log("Stalled Called");
                $("#bottomPlayPauseButton").css("background-image", "url(" + IniteditMusic.player.config.icon.loading + ")");
                $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.loading + ")");
                if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid == IniteditMusic.player.audio.current.musicid) {
                        $("#fullMusicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.loading + ")");
                    }
                }
            },
            audioError: function (e) {
                IniteditMusic.notification.toast.addMessageBox(1, "Music Not Found.");
                //IniteditMusic.player.next();

            },
            getNext: function () {
                if (IniteditMusic.player.audio.playlist != null) {
                    for (var i = 0; i < this.playlist.length - 1; i++)
                    {
                        var m = this.playlist[i];
                        if (m.musicid == this.current.musicid)
                        {
                            return this.playlist[i + 1];
                        }
                    }
                    if (IniteditMusic.player.config.repeat) {
                        if (this.playlist != null) {
                            var musicCount = this.playlist.length;
                            var lastMusic = this.playlist[musicCount - 1];
                            if (lastMusic.musicid == IniteditMusic.player.audio.current.musicid) {
                                return this.playlist[0];
                            }
                        }
                    }
                }

                return false;
            },
            getPrevious: function () {
                if (IniteditMusic.player.audio.playlist != null) {
                    for (var i = 0; i < this.playlist.length; i++)
                    {
                        var m = this.playlist[i];
                        if (m.musicid == this.current.musicid)
                        {
                            if (i == 0) {
                                return false;
                            } else {
                                return this.playlist[i - 1];
                            }
                        }
                    }
                }
                return false;
            },
            isRepeat: function () {
                return IniteditMusic.player.config.repeat;
            }
        },
        toggleSetting: function (id) {
            $(".musicSettingItem_" + id).slideToggle("fast");
        },
        showOnMouseEnter: function (id) {

//            var m = IniteditMusic.recentMusic.getByIdAny(id);
//            if (m !== false)
//            {
//                $(".musicPlayPauseButtonItem_" + id).show();
//            }
            $(".musicPlayPauseButtonItem_" + id).show();
        },
        hideOnMouseLeave: function (id) {
//            var m = IniteditMusic.recentMusic.getByIdAny(id);
//            if (m !== false)
//            {
//                if (IniteditMusic.player.audio.current == null) {
//                    $(".musicPlayPauseButtonItem_" + id).hide();
//                } else {
//                    if ((m.musicid != IniteditMusic.player.audio.current.musicid)) {
//                        $(".musicPlayPauseButtonItem_" + id).hide();
//                    }
//                }
//            }
            if (IniteditMusic.player.audio.current == null) {
                $(".musicPlayPauseButtonItem_" + id).hide();
            } else {
                if ((id != IniteditMusic.player.audio.current.musicid)) {
                    $(".musicPlayPauseButtonItem_" + id).hide();
                }
            }

        },
        toggleMusicOnMouseClick: function (id) {
            var m = IniteditMusic.recentMusic.getByIdAny(id);
            if (IniteditMusic.player.audio.playlist == null) {
                IniteditMusic.player.audio.playlist = IniteditMusic.recentMusic.music;
            } else {
                if (m != null) {
                    var playlist = IniteditMusic.player.audio.playlist;
                    var isInPlaylist = false;
                    if (playlist != null) {
                        for (var i = 0; i < playlist.length; i++) {
                            var music = playlist[i];
                            if (music.musicid == id) {
                                isInPlaylist = true;
                            }
                        }
                        if (!isInPlaylist) {
                            IniteditMusic.player.audio.playlist.push(m);
                        }
                    }
                }
            }

            if (m !== false)
            {
                $(".backgroundBackgroundImage_" + m.musicid).addClass("circularRotate");

                if (IniteditMusic.player.audio.current == null) {
                    IniteditMusic.player.play(m);
                } else if (m.musicid == IniteditMusic.player.audio.current.musicid) {
                    //Clicked on playing Music Card
                    IniteditMusic.player.toggle();

                } else {

                    var music = IniteditMusic.player.audio.current;
                    $(".updateWaveformPlayedCanvas_" + music.musicid).css("width", 0 + "%");


                    //Clicked ON New Music
                    $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                    $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).hide();
                    $(".backgroundBackgroundImage_" + IniteditMusic.player.audio.current.musicid).removeClass("circularRotate");

                    IniteditMusic.player.play(m);
                }
                if (IniteditMusic.recentMusic.fullScreenMusic != null) {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid != IniteditMusic.player.audio.current.musicid) {
                        $(".fullMusicPlayPauseButtonItem_" + IniteditMusic.recentMusic.fullScreenMusic.musicid).css("background-image", "url(" + IniteditMusic.player.config.icon.play + ")");
                    }
                }
                if ($("#bottomPlatlistContainer").is(":visible")) {
                    IniteditMusic.player.generateBottomPlaylist();
                }


            }
            this.updateBottomMusicProgressContainer();


        },
        updateBottomMusicProgressContainer: function () {
            var sum = 0;
            $('.bottomPlayer > *').not(".bottomMusicTimeContainer").each(function () {
                sum += $(this).width();
            });
            var timeSum = $(".bottomMusicTimeContainer >li:first-child").width() + $(".bottomMusicTimeContainer >li:last-child").width()
            var total = $(".bottomPlayer").width() - 1;
            $(".bottomMusicProgressContainer").css("min-width", parseInt((total - sum - timeSum)) + "px");
        },
        showPlaylistOnClick: function () {

            if (!$("#bottomPlatlistContainer").is(":visible")) {
                setTimeout(function () {
                    $(window).on("click", IniteditMusic.player.hidePlaylistOnClickShortcut);
                    $("#bottomPlatlistContainer").on("click", IniteditMusic.player.onClickStopPropagation);
                }, 0);
            }
            $("#bottomPlatlistContainer").slideDown();
            this.generateBottomPlaylist(true);
        },
        onClickStopPropagation: function (e) {
            e.stopPropagation();
        },
        hidePlaylistOnClick: function () {
            $(window).off("click", IniteditMusic.player.hidePlaylistOnClickShortcut);
            $("#bottomPlatlistContainer").off("click", IniteditMusic.player.onClickStopPropagation);
            $("#bottomPlatlistContainer").slideUp();
        },
        hidePlaylistOnClickShortcut: function () {
            $(window).off("click", IniteditMusic.player.hidePlaylistOnClickShortcut);
            $("#bottomPlatlistContainer").off("click", IniteditMusic.player.onClickStopPropagation);
            $("#bottomPlatlistContainer").slideUp();
        },
        clearPlaylistOnClick: function () {
            IniteditMusic.player.audio.playlist = [];
            if (IniteditMusic.player.audio.current != null) {
                IniteditMusic.player.audio.playlist.push(IniteditMusic.player.audio.current);
            }
            this.generateBottomPlaylist(false);
        },
        playFromPlaylistOnClick: function (id) {

            this.toggleMusicOnMouseClick(id)

            this.generateBottomPlaylist();
        },
        removeFromPlaylistOnClick: function (e, id) {
            e.stopPropagation();
            var playlist = IniteditMusic.player.audio.playlist;
            if (playlist != null) {
                for (var i = 0; i < playlist.length; i++) {
                    var music = playlist[i];
                    if (music.musicid == id) {
                        $("#bottomPlaylistItem_" + id).remove();
                        playlist.splice(i, 1);
                        break;
                    }
                }
            }
        },
        generateBottomPlaylist: function (center) {

            var music = IniteditMusic.player.audio.playlist;
            if (music == null) {
                var emptyPlaylist = $(".playlist-empty-template").prop("innerHTML");
                $("#bottomPlatlistItemContainer").html(emptyPlaylist);
                return;
            }
            var musicCount = music.length;
            var template = $("#bottomPlaylistItemTemplateContainer").prop('innerHTML');
            $("#bottomPlatlistItemContainer").html("");
            for (var i = 0; i < musicCount; i++)
            {
                var musicInfo = music[i];
                var txtTemplate = template;
                txtTemplate = txtTemplate.replace(/\{\{musicid\}\}/g, musicInfo.musicid);
                txtTemplate = txtTemplate.replace(/\{\{title\}\}/g, musicInfo.title);
                txtTemplate = txtTemplate.replace(/\{\{username\}\}/g, musicInfo.user.name);
                txtTemplate = txtTemplate.replace(/\{\{imagethumb\}\}/g, musicInfo.image.thumb);
                $("#bottomPlatlistItemContainer").append(txtTemplate);
            }
            if (musicCount > 0) {
                var m = IniteditMusic.player.audio.current;
                $("#bottomPlaylistItem_" + m.musicid).addClass("highlight");

                // Scroll to the center
                if (center) {
                    var $parentDiv = $("#bottomPlatlistItemContainer");
                    var $innerListItem = $("#bottomPlaylistItem_" + m.musicid);
                    if ($innerListItem.length > 0) {
                        $parentDiv.scrollTop($parentDiv.scrollTop() + $innerListItem.position().top
                                - $parentDiv.height() / 2 - $innerListItem.height() / 2);
                    }
                }
            } else {
                //Playlist is empty
                var emptyPlaylist = $(".playlist-empty-template").prop("innerHTML");
                $("#bottomPlatlistItemContainer").html(emptyPlaylist);
            }

        },
        next: function () {

            var m = IniteditMusic.player.audio.getNext();
            IniteditMusic.player.toggleMusicOnMouseClick(m.musicid);
        },
        previous: function () {

            var m = IniteditMusic.player.audio.getPrevious();
            IniteditMusic.player.toggleMusicOnMouseClick(m.musicid);
        },
        waveform: {
            config: {
                defaultType: 1,
                music: null
            },
            generate: function ($waveformContainer, musicObject)
            {
                this.config.music = musicObject;
                var type = IniteditMusic.storage.local.getWaveformType();
                if (type == 1)
                {
                    IniteditMusic.player.waveform.generateNormal($waveformContainer, musicObject);
                } else if (type == 2) {
                    IniteditMusic.player.waveform.generateThumb($waveformContainer, musicObject);
                }
            },
            regenerate: function ($waveformContainer)
            {
                console.log("Removed Regenrate Functionality")
//                if (this.config.music != null) {
//                    this.generate($waveformContainer, this.config.music);
//                }
            },
            generateNormal: function ($waveformContainer, musicObject)
            {
//                var $waveformContainer = $(".waveformContainer");
                var normalCanvas = $waveformContainer.find(".normalWaveform").get(0);
                var playedCanvas = $waveformContainer.find(".playedWaveform").get(0);
                var seekCanvas = $waveformContainer.find(".seekWaveform").get(0);

                if (normalCanvas == undefined || normalCanvas == null) {
                    throw new Error("Canvas Element not found to generate waveform");
                }

                var ctx = normalCanvas.getContext("2d");
                var seekCanvasCtx = seekCanvas.getContext("2d");
                var playedCanvasCtx = playedCanvas.getContext("2d");
                var width = $(normalCanvas).width();
                var height = $(normalCanvas).height();
                $(normalCanvas).prop("width", width);
                $(normalCanvas).prop("height", height);

                $(playedCanvas).prop("width", width).css("width", width + "px");
                $(playedCanvas).prop("height", height).css("height", height + "px");
                $(seekCanvas).prop("width", width).css("width", width + "px");
                $(seekCanvas).prop("height", height).css("height", height + "px");



                var img = new Image();
                img.onload = function ()
                {
                    ctx.drawImage(img, 0, 0, img.naturalWidth, img.naturalHeight, 0, 0, width, height);

                    var imageData = ctx.getImageData(0, 0, width, height);
                    var len = imageData.data.length;
                    var start = Math.ceil((len * 0.77));
                    var startMiddleLine = Math.ceil((len * 0.75));



                    for (var i = 0; i < imageData.data.length; i += 4)
                    {
                        if (imageData.data[i] != 0) {
                            imageData.data[i] = 50
                        }
                        if (imageData.data[i + 1] != 0) {
                            imageData.data[i + 1] = 50
                        }
                        if (imageData.data[i + 2] != 0) {
                            imageData.data[i + 2] = 50;
                        }
                    }
                    for (var i = startMiddleLine; i < startMiddleLine + width * 8; i += 4)
                    {
                        imageData.data[i + 3] = 0;
                    }

                    for (var i = start; i < len; i += 4)
                    {
//                        imageData.data[i] = 150
//                        imageData.data[i + 1] = 150
//                        imageData.data[i + 2] = 150;
                        if (imageData.data[i + 3] != 0)
                            imageData.data[i + 3] = 120;
                    }

                    ctx.putImageData(imageData, 0, 0);


                    var imageData = ctx.getImageData(0, 0, width, height);
                    for (var i = 0; i < imageData.data.length; i += 4)
                    {
                        if (imageData.data[i] != 0) {
                            imageData.data[i] = 120

                        }
                        if (imageData.data[i + 1] != 0) {
                            imageData.data[i + 1] = 255
                        }
                        if (imageData.data[i + 2] != 0) {
                            imageData.data[i + 2] = 0;
                        }
                    }

                    for (var i = start; i < len; i += 4)
                    {
                        if (imageData.data[i + 3] != 0)
                            imageData.data[i + 3] = 120;
                    }

                    seekCanvasCtx.putImageData(imageData, 0, 0);


                    var imageData = ctx.getImageData(0, 0, width, height);
                    for (var i = 0; i < imageData.data.length; i += 4)
                    {
                        if (imageData.data[i] != 0) {
                            imageData.data[i] = 255
                        }
                        if (imageData.data[i + 1] != 0) {
                            imageData.data[i + 1] = 120
                        }
                        if (imageData.data[i + 2] != 0) {
                            imageData.data[i + 2] = 0;
                        }
                    }

                    for (var i = start; i < len; i += 4)
                    {
                        if (imageData.data[i + 3] != 0)
                            imageData.data[i + 3] = 120;
                    }

                    playedCanvasCtx.putImageData(imageData, 0, 0);
                }
                img.src = musicObject.image.waveform;

            },
            generateThumb: function ($waveformContainer, musicObject)
            {

//                var $waveformContainer = $(".waveformContainer");
                var normalCanvas = $waveformContainer.find(".normalWaveform").get(0);
                var playedCanvas = $waveformContainer.find(".playedWaveform").get(0);
                var seekCanvas = $waveformContainer.find(".seekWaveform").get(0);
                var thumbCanvas = $waveformContainer.find(".thumbWaveform").get(0);

                if (normalCanvas == undefined || normalCanvas == null) {
                    throw new Error("Canvas Element not found to generate waveform");
                }


                var ctx = normalCanvas.getContext("2d");
                var seekCanvasCtx = seekCanvas.getContext("2d");
                var playedCanvasCtx = playedCanvas.getContext("2d");
                var thumbCanvasCtx = thumbCanvas.getContext("2d");

                var width = $(normalCanvas).width();
                var height = $(normalCanvas).height();
                $(normalCanvas).prop("width", width);
                $(normalCanvas).prop("height", height);

                $(playedCanvas).prop("width", width).css("width", width + "px");
                $(playedCanvas).prop("height", height).css("height", height + "px");
                $(seekCanvas).prop("width", width).css("width", width + "px");
                $(seekCanvas).prop("height", height).css("height", height + "px");
                $(thumbCanvas).prop("width", width).css("width", width + "px");
                $(thumbCanvas).prop("height", height).css("height", height + "px");

                var img = new Image();
                img.onload = function ()
                {
                    ctx.drawImage(img, 0, 0, img.naturalWidth, img.naturalHeight, 0, 0, width, height);
                    thumbCanvasCtx.drawImage(thumbImage, 0, 0, thumbImage.naturalWidth, thumbImage.naturalHeight, 0, 0, width, height);
                    var imageData = ctx.getImageData(0, 0, width, height);
                    var imageDataThumb = thumbCanvasCtx.getImageData(0, 0, width, height);

                    for (var i = 0; i < imageData.data.length; i += 4)
                    {
                        var brightness = 0.34 * imageDataThumb.data[i] + 0.5 * imageDataThumb.data[i] + 0.16 * imageDataThumb.data[i];
                        if (imageData.data[i] != 0) {
                            imageData.data[i + 3] = 255;
                            imageData.data[i] = brightness;
                        }
                        if (imageData.data[i + 1] != 0) {
                            imageData.data[i + 3] = 255;
                            imageData.data[i + 1] = brightness;
                        }
                        if (imageData.data[i + 2] != 0) {
                            imageData.data[i + 3] = 255;
                            imageData.data[i + 2] = brightness;
                        }
                    }
                    seekCanvasCtx.putImageData(imageData, 0, 0);


                    var imageData = ctx.getImageData(0, 0, width, height);
                    for (var i = 0; i < imageData.data.length; i += 4)
                    {
                        if (imageData.data[i] != 0) {

                            imageData.data[i] = imageDataThumb.data[i]
                            //Make to make opacity to 1->255
                            imageData.data[i + 3] = 255;
                        }
                        if (imageData.data[i + 1] != 0) {

                            imageData.data[i + 1] = imageDataThumb.data[i + 1]
                            //Make to make opacity to 1->255
                            imageData.data[i + 3] = 255;
                        }
                        if (imageData.data[i + 2] != 0) {

                            imageData.data[i + 2] = imageDataThumb.data[i + 2]
                            //Make to make opacity to 1->255
                            imageData.data[i + 3] = 255;
                        }
                    }
                    playedCanvasCtx.putImageData(imageData, 0, 0);
                }

                var thumbImage = new Image();
                thumbImage.onload = function () {
                    img.src = musicObject.image.waveform;
                }
                IniteditMusic.log(musicObject);
                thumbImage.src = musicObject.image.original;
            },
            showSeekBar: function (element, e) {
                var $waveformContainer = $(element);
                var m = IniteditMusic.player.audio.current;
                if (m != null) {
                    var offsetElement = $waveformContainer.offset();
                    var width = $waveformContainer.find(".normalWaveform").width();
                    if (offsetElement != undefined) {
                        var x = (e.pageX - offsetElement.left);
                        var percentageSeek = (x / width) * 100;
                        $waveformContainer.find(".seekWaveformContainer_" + m.musicid).css("width", percentageSeek + "%");
                    }
                }
            },
            hideSeekBar: function (element, e) {
                var $waveformContainer = $(element);
                var m = IniteditMusic.player.audio.current;
                if (m != null) {
                    $waveformContainer.find(".seekWaveformContainer_" + m.musicid).css("width", 0 + "%");
                }
            },
            seekOnClick: function (element, e) {
                var $waveformContainer = $(element);
                var m = IniteditMusic.player.audio.current;
                if (m != null) {
                    var offsetElement = $waveformContainer.offset();
                    var width = $waveformContainer.find(".normalWaveform").width();
                    if (offsetElement != undefined) {
                        var x = (e.pageX - offsetElement.left);

                        var percentageSeek = (x / width) * 100;

                        IniteditMusic.player.audio.skipTimeBy(percentageSeek);
                    }
                }
            },
            seekOnClickPlay: function (element, e) {
                var $waveformContainer = $(element);
                var m = $waveformContainer.data("music");
                if (m != null) {

                    if (IniteditMusic.player.isPlaying()) {
                        if (IniteditMusic.player.audio.current.musicid == m.musicid) {
                            var offsetElement = $waveformContainer.offset();
                            var width = $waveformContainer.find(".normalWaveform").width();
                            if (offsetElement != undefined) {
                                var x = (e.pageX - offsetElement.left);

                                var percentageSeek = (x / width) * 100;

                                IniteditMusic.player.audio.skipTimeBy(percentageSeek);
                            }
                        } else {
//                            IniteditMusic.player.play(m);
                            IniteditMusic.player.toggleMusicOnMouseClick(m.musicid);
                        }
                    } else {
//                        IniteditMusic.player.play(m);
                        IniteditMusic.player.toggleMusicOnMouseClick(m.musicid);
                    }
                }
            }

        },
        playlist: {
            bottom: {
                onDragStartedMusicItem: function (ev, id) {
                    ev.dataTransfer.effectAllowed = 'copy';
                    ev.dataTransfer.setData("text", id + "");
                },
                allowDropMusicItem: function (ev) {
                    ev.preventDefault();
                },
                onDropMusicItem: function (ev, id) {
                    ev.preventDefault();
                    var previousID = ev.dataTransfer.getData("text");

                    if (IniteditMusic.player.audio.playlist == null) {
                        IniteditMusic.player.audio.playlist = new Array();
                    }

                    var startMusic = this.getByID(previousID);
                    var endMusic = this.getByID(id);

                    if (startMusic == false) {
                        //When Droped outside of the playlist
                        this.addMusicItemIntoPlaylistAfter(previousID, endMusic);
                    } else {

                        var startMusicIndex = IniteditMusic.player.audio.playlist.indexOf(startMusic);
                        var endMusicIndex = IniteditMusic.player.audio.playlist.indexOf(endMusic);
                        var tmpStart = IniteditMusic.player.audio.playlist[startMusicIndex];
                        var maximumCount = IniteditMusic.player.audio.playlist.length;

                        if (startMusicIndex < endMusicIndex) {
                            IniteditMusic.player.audio.playlist.splice(endMusicIndex + 1, 0, startMusic);
                            IniteditMusic.player.audio.playlist.splice(startMusicIndex, 1);
                        } else {
                            IniteditMusic.player.audio.playlist.splice(endMusicIndex, 0, startMusic);
                            IniteditMusic.player.audio.playlist.splice(startMusicIndex + 1, 1);
                        }
                    }
                    IniteditMusic.player.generateBottomPlaylist();
                }
                ,
                onMenuButtonDropMusicItem: function (ev) {
                    ev.preventDefault();
                    var previousID = ev.dataTransfer.getData("text");

                    if (IniteditMusic.player.audio.playlist == null) {
                        IniteditMusic.player.audio.playlist = new Array();
                    }

                    var startMusic = IniteditMusic.recentMusic.getByIdAny(previousID);

                    var startMusicIndex = IniteditMusic.player.audio.playlist.indexOf(startMusic);

                    var maximumCount = IniteditMusic.player.audio.playlist.length;
                    if (startMusic && startMusicIndex < 0)
                    {
                        IniteditMusic.player.audio.playlist.push(startMusic);
                        IniteditMusic.notification.toast.addMessageBox(0, "Added to playlist");
                        IniteditMusic.player.generateBottomPlaylist();
                    } else {

                    }


                },
                addMusicItemIntoPlaylistAfter: function (id, after) {
                    var startMusic = IniteditMusic.recentMusic.getByIdAny(id);
                    var afterMusicIndex = IniteditMusic.player.audio.playlist.indexOf(after);
                    IniteditMusic.player.audio.playlist.splice(afterMusicIndex + 1, 0, startMusic);
                }
                ,
                getByID: function (id) {
                    var musicCount = IniteditMusic.player.audio.playlist.length;
                    for (var i = 0; i < musicCount; i++)
                    {
                        var m = IniteditMusic.player.audio.playlist[i];
                        if (m.musicid == id) {
                            return m;
                        }
                    }
                    return false;
                }
            }
        },
        onClickRepeatToggle: function () {
            IniteditMusic.player.config.repeat = !IniteditMusic.player.config.repeat;
            IniteditMusic.storage.local.setRepeat(IniteditMusic.player.config.repeat);
            this.updateRepeatUI();
            IniteditMusic.player.nextAndPreviousButtonCheck();

        },
        updateRepeatUI: function () {
            var icon = null;
            if (IniteditMusic.player.config.repeat)
            {
                icon = IniteditMusic.player.config.icon.repeathighlight;

            } else {
                icon = IniteditMusic.player.config.icon.repeat;
            }

            $("#bottomRepeatButton").css("background-image", "url(" + icon + ")")
        },
        onClickPlayFromPlaylistItem: function (element, id) {
            
            var music = $(element).data("playlist");
            var playlistid = $(element).data("playlistid");
            if (music != null) {
                for (var i = 0; i < music.length; i++) {
                    IniteditMusic.hObject.addPlaylist(music[i]);
                }
                var m= IniteditMusic.recentMusic.getByIdAny(id);
                var $container = $(".playlist_"+playlistid);
                $container.addClass("musicPlaylistItem_"+id);
                $container.find(".backgroundBackgroundImage").css("background-image","url("+m.image.thumb+")");
                $container.find(".thumburl").attr("href",m.musicurl)
                IniteditMusic.waveform.add($(".musicPlaylistItem_" + id).find(".waveform"), id);
            }
            IniteditMusic.player.toggleMusicOnMouseClick(id);
            
        }
    },
    waveform: {
        add: function ($container, musicid) {
            var music = IniteditMusic.recentMusic.getByIdAny(musicid);

            var wave = IniteditMusic.player.waveform;
            var $template = $($("#waveformContainerTemplate").prop("innerHTML"));


            $template.find(".updateWaveformPlayedCanvas").addClass("updateWaveformPlayedCanvas_" + musicid);
            $template.find(".seekWaveformContainer").addClass("seekWaveformContainer_" + musicid);
            $container.html($template);
            $container.find("> .waveContainer").data("music", music);

            wave.generate($container, music);
            return wave;
        },
    },
    playlist: {
        showAddToPlaylist: function (musicObject) {
            $("#addToPlaylistContainer").fadeIn("fast", function () {
                $("#main").hide();
            });

            $(window).on("keyup", this.hideAddToPlaylistFullScreenShortcut);

            IniteditMusic.recentObject.recentAjax = $.ajax({
                type: "POST",
                url: "/ajax/playlist/add",
                data: {info: JSON.stringify({music: musicObject})},
                dataType: "json",
                success: function (data) {
                    IniteditMusic.recentObject.response = data;
                    $("#addToPlaylistContainer #addToPlaylist").html(data.data);
                    $("#addToPlaylistContainer #addToPlaylist a").on("click", IniteditMusic.helper.anchorClick);
                    if ($("#addToPlaylistContainer #addToPlaylist .playlistItem").length == 0)
                    {
                        IniteditMusic.playlist.showAddToPlaylistNewContainer();
                    } else {
                        IniteditMusic.playlist.showAddToPlaylistAddContainer();
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                }
            });
        },
        showAddToPlaylistFullScreen: function (id) {

            this.showAddToPlaylist(IniteditMusic.recentMusic.fullScreenMusic);
        },
        hideAddToPlaylistFullScreen: function () {
            $("#main").show();
            $("#addToPlaylistContainer").fadeOut();
        },
        hideAddToPlaylistFullScreenShortcut: function (e) {
            if (e.keyCode == 27) {
                IniteditMusic.playlist.hideAddToPlaylistFullScreen();
                $(window).off("keyup", this.hideAddToPlaylistFullScreenShortcut);
            }
        },
        createNewPlaylist: function (musicObject) {
            var playlist = $("#newPlaylistName").val();
            var privacy = $("#playlistPrivacy").is(":checked");
            var info = {
                playlist: playlist,
                privacy: privacy,
                music: musicObject
            };
            if (playlist.length == 0) {
                $("#addNewplaylistError").html("Name is required").slideDown();
            }
            IniteditMusic.recentObject.recentAjax = $.ajax({
                type: "POST",
                url: "/ajax/playlist/create",
                data: {info: JSON.stringify(info)},
                dataType: "json",
                success: function (data) {
                    IniteditMusic.recentObject.response = data;
                    if (data.code == 1) {
                        IniteditMusic.playlist.hideAddToPlaylistFullScreen();
                        IniteditMusic.notification.toast.addMessageBox(0, data.message);
                    } else {
                        $("#addNewplaylistError").html(data.message).slideDown();
//                        IniteditMusic.notification.toast.addMessageBox(1,data.message);
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                }
            });
        },
        onClickCreateNewPlaylistFullScreen: function (id) {
            var m = IniteditMusic.recentMusic.getByIdAny(id);
            $("#addNewplaylistError").slideUp();
            this.createNewPlaylist(m);
        },
        showAddToPlaylistNewContainer: function () {
            $("#addToPlaylistNewContainer").show();

            $("#addToPlaylistAddContainer").hide();
            $("#addToPlaylistNewContainerMenu").addClass("highlight");
            $("#addToPlaylistAddContainerMenu").removeClass("highlight");
        },
        showAddToPlaylistAddContainer: function () {
            $("#addToPlaylistNewContainer").hide().removeClass("highlight");
            $("#addToPlaylistAddContainer").show().addClass("highlight");
            $("#addToPlaylistNewContainerMenu").removeClass("highlight");
            $("#addToPlaylistAddContainerMenu").addClass("highlight");
        },
        addToPlaylist: function (musicObject, playlist, url) {
            IniteditMusic.recentObject.recentAjax = $.ajax({
                type: "POST",
                url: "/ajax/playlist/addtoplaylist",
                data: {info: JSON.stringify({music: musicObject, playlist: playlist, url: url})},
                dataType: "json",
                success: function (data) {
                    IniteditMusic.recentObject.response = data;
                    if (data.code == 1) {
                        $("#addButtonPlaylist_" + url + "_" + musicObject.musicid).hide();
                        $("#removeButtonPlaylist_" + url + "_" + musicObject.musicid).show();
                    } else {
                        $("#addToPlaylistError").html(data.message).slideDown();
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                }
            });
        },
        onClickAddToPlaylistFullScreen: function (id, playlist, url) {
            $("#addToPlaylistError").slideUp();
            var m = IniteditMusic.recentMusic.getByIdAny(id);
            this.addToPlaylist(m, playlist, url);
        },
        removeFromPlaylist: function (musicObject, playlist, url) {
            IniteditMusic.recentObject.recentAjax = $.ajax({
                type: "POST",
                url: "/ajax/playlist/removefromplaylist",
                data: {info: JSON.stringify({music: musicObject, playlist: playlist, url: url})},
                dataType: "json",
                success: function (data) {
                    IniteditMusic.recentObject.response = data;
                    if (data.code == 1) {
                        $("#addButtonPlaylist_" + url + "_" + musicObject.musicid).show();
                        $("#removeButtonPlaylist_" + url + "_" + musicObject.musicid).hide();
                    } else {
                        $("#addToPlaylistError").html(data.message).slideDown();
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                }
            });
        },
        onClickRemoveFromPlaylistFullScreen: function (id, playlist, url) {
            $("#addToPlaylistError").slideUp();
            var m = IniteditMusic.recentMusic.getByIdAny(id);
            this.removeFromPlaylist(m, playlist, url);
        },
        /*
         * For Music Items
         */

        onClickItemShowAddToPlatlist: function (id) {

            var m = IniteditMusic.recentMusic.getByIdAny(id);
            this.showAddToPlaylist(m);

        },
        startAnimation: function () {
            var jqueryObject = $('.playlistBackgroundScroll img');

            if (jqueryObject.length < 6) {
                return;
            }

            var first = jqueryObject.get(0);
            var firstJquery = $(first);
            var width = firstJquery.width();


            /*
             * 300px - 2000 mili second
             * width - x seconds
             * x  = 2000 * width/300 
             */
            var time = 6.6667 * width;
            firstJquery.animate({marginLeft: "-" + width + "px"}, {
                duration: time,
//                easing: "easeInOutExpo",
                complete: function () {
                    $(this).css("margin-left", 0);
                    $('.playlistBackgroundScroll').append(this);
                    IniteditMusic.playlist.startAnimation();
                    //setTimeout(IniteditMusic.playlist.startAnimation, 1900);
                }
            });
        }
    },
    music: {
        like: {
            likeMusic: function (musicObject) {
                IniteditMusic.recentObject.recentAjax = $.ajax({
                    type: "POST",
                    url: "/ajax/music/like",
                    data: {info: JSON.stringify({music: musicObject})},
                    dataType: "json",
                    beforeSend: function () {
                        $("#musiclike_" + musicObject.musicid).hide();
                        $("#musicliked_" + musicObject.musicid).show();
                    },
                    success: function (data) {
                        IniteditMusic.recentObject.response = data;
                        if (data.code == 1) {
                            $("#musiclike_" + musicObject.musicid).hide();
                            $("#musicliked_" + musicObject.musicid).show();
                        } else {
                            IniteditMusic.notification.toast.addMessageBox(1, data.message);
                            $("#musiclike_" + musicObject.musicid).show();
                            $("#musicliked_" + musicObject.musicid).hide();
                        }
                    },
                    failure: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg + "</div>");
                        IniteditMusic.recentObject.response = errMsg;
                    },
                    error: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg.responseText + "</div>");
                        $("#musiclike_" + musicObject.musicid).show();
                        $("#musicliked_" + musicObject.musicid).hide();
                    }
                });
            },
            onClickLikeMusic: function (id) {
                var m = IniteditMusic.recentMusic.getByIdAny(id);

                this.likeMusic(m);
            },
            unlikeMusic: function (musicObject) {
                IniteditMusic.recentObject.recentAjax = $.ajax({
                    type: "POST",
                    url: "/ajax/music/unlike",
                    data: {info: JSON.stringify({music: musicObject})},
                    dataType: "json",
                    beforeSend: function () {
                        $("#musiclike_" + musicObject.musicid).show();
                        $("#musicliked_" + musicObject.musicid).hide();
                    },
                    success: function (data) {
                        IniteditMusic.recentObject.response = data;
                        if (data.code == 1) {
                            $("#musiclike_" + musicObject.musicid).show();
                            $("#musicliked_" + musicObject.musicid).hide();
                        } else {
                            IniteditMusic.notification.toast.addMessageBox(1, data.message);
                            $("#musiclike_" + musicObject.musicid).hide();
                            $("#musicliked_" + musicObject.musicid).show();
                        }
                    },
                    failure: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg + "</div>");
                        IniteditMusic.recentObject.response = errMsg;
                    },
                    error: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg.responseText + "</div>");
                        $("#musiclike_" + musicObject.musicid).hide();
                        $("#musicliked_" + musicObject.musicid).show();
                    }
                });
            },
            onClickUnlikeMusic: function (id) {
                var m = IniteditMusic.recentMusic.getByIdAny(id);

                this.unlikeMusic(m);
            },
        },
        viewCount: function (musicObject) {
            IniteditMusic.recentObject.recentAjax = $.ajax({
                type: "POST",
                url: "/ajax/music/viewCount",
                data: {info: JSON.stringify({music: musicObject})},
                dataType: "json",
                success: function (data) {
                    IniteditMusic.recentObject.response = data;

                },
                failure: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusic.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusic.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                }
            });
        },
        share: {
            config: {
                twitter: "https://twitter.com/share?url=",
                google: "https://plus.google.com/share?url="
            },
            showShareDialog: function (id) {
                var hostDomain = window.location.protocol + "//" + window.location.host
                $("#shareDialogBoxContainer").fadeIn();
                var m = IniteditMusic.recentMusic.getByIdAny(id);
                $("#twitterShare").prop("href", this.config.twitter + encodeURIComponent(hostDomain + m.musicurl));
                $("#googleShare").prop("href", this.config.google + hostDomain + m.musicurl);
                $(window).on("keyup", this.hideShareDialogShortcut);
            },
            hideShareDialog: function () {
                $("#shareDialogBoxContainer").fadeOut();
            },
            hideShareDialogShortcut: function (e) {

                if (e.keyCode == 27) {

                    IniteditMusic.music.share.hideShareDialog();
                    $(window).off("keyup", this.hideShareDialogShortcut);
                }
            },
        },
        privacy: {
            onClickShowFullScreenPrivacy: function () {
                $("#fullMusicChangePrivacyBox").slideToggle();
            },
            changeFullMusicPrivacy: function (musicObject, privacy) {
                IniteditMusic.recentObject.recentAjax = $.ajax({
                    type: "POST",
                    url: "/ajax/music/changeprivacy",
                    data: {info: JSON.stringify({music: musicObject, privacy: privacy})},
                    dataType: "json",
                    success: function (data) {
                        IniteditMusic.recentObject.response = data;
                        if (data.code == 1) {
                            IniteditMusic.notification.toast.addMessageBox(0, data.message);
                        } else {
                            IniteditMusic.notification.toast.addMessageBox(1, data.message);
                        }
                    },
                    failure: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg + "</div>");
                        IniteditMusic.recentObject.response = errMsg;
                    },
                    error: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg.responseText + "</div>");
                        $("#musiclike_" + musicObject.musicid).show();
                        $("#musicliked_" + musicObject.musicid).hide();
                    }
                });
            },
            onChangeFullMusicPrivacy: function (id) {
                var m = IniteditMusic.recentMusic.getByIdAny(id);
                var privacy = $("#fullMusicPrivacySelect").val();
                this.changeFullMusicPrivacy(m, privacy);
            }
        }
    },
    user: {
        upload: {
            config: {
                profileimage: null,
                coverImage: null,
                saveProfileImageAjax: null,
                saveCoverImageAjax: null
            },
            profile: function () {
                if (this.config.profileimage == null)
                {
                    return;
                }
                var formData = new FormData();
                formData.append('image', this.config.profileimage);

                this.config.saveProfileImageAjax = $.ajax({
                    url: '/ajax/upload/userprofile',
                    data: formData,
                    contentType: false,
                    processData: false,
                    type: "POST",
                    dataType: "json",
                    success: function (data) {
                        IniteditMusic.recentObject.response = data;
                        IniteditMusic.log(data);
                        if (data.code == 1) {
                            $("#userProfileImage").attr("data-img", data.image);
                            $("#userProfileImage").attr("src", data.image);
                            IniteditMusic.user.upload.config.profileimage = null;
                            $("#profileImageActionContainer").slideUp();
                        } else {
                            IniteditMusic.notification.toast.addMessageBox(1, data.message);
                        }
                    },
                    failure: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg + "</div>");
                        IniteditMusic.recentObject.response = errMsg;
                    },
                    error: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg.responseText + "</div>");
                        console.log(errMsg);
                    },
                    abort: function () {
                        var img = $("#userProfileImage").attr("data-img");
                        $("#userProfileImage").attr("src", img);
                        this.config.profileimage = null;
                        $("#profileImageActionContainer").slideUp();
                    }
                });
            },
            onChangeProfile: function () {
                IniteditMusic.log(this);
                var files = $("#userProfileImageInput").prop("files");
                if (files && files[0]) {
                    var input = {
                        id: "userProfileImage",
                        f: files
                    }
                    IniteditMusic.upload.music.previewImageReadURL(input);
                    this.config.profileimage = files[0];
                    $("#profileImageActionContainer").slideDown();
                }
            },
            onCancelProfile: function () {
                var img = $("#userProfileImage").attr("data-img");
                $("#userProfileImage").attr("src", img);
                this.config.profileimage = null;
                $("#profileImageActionContainer").slideUp();
                if (this.config.saveProfileImageAjax != null) {
                    this.config.saveProfileImageAjax.abort();
                }
            },
            onSaveProfile: function () {
                this.profile();
            },
            cover: function () {
                if (IniteditMusic.user.upload.config.coverImage == null)
                {
                    return;
                }
                var formData = new FormData();
                formData.append('image', this.config.coverImage);

                this.config.saveCoverImageAjax = $.ajax({
                    url: '/ajax/upload/usercover',
                    data: formData,
                    contentType: false,
                    processData: false,
                    type: "POST",
                    dataType: "json",
                    success: function (data) {
                        IniteditMusic.recentObject.response = data;
                        IniteditMusic.log(data);
                        if (data.code == 1) {
                            $("#userCoverImage").attr("data-img", data.image);
                            $("#userCoverImage").css("background-image", "url(" + data.image + ")");
                            IniteditMusic.user.upload.config.coverImage = null;
                            $("#coverImageActionContainer").slideUp();
                        } else {
                            IniteditMusic.notification.toast.addMessageBox(1, data.message);
                        }
                    },
                    failure: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg + "</div>");
                        IniteditMusic.recentObject.response = errMsg;
                    },
                    error: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg.responseText + "</div>");
                        console.log(errMsg);
                    },
                    abort: function () {
                        var img = $("#userCoverImage").attr("data-img");
                        $("#userCoverImage").css("background-image", "url(" + img + ")");
                        this.config.profileimage = null;
                        $("#coverImageActionContainer").slideUp();
                    }
                });
            },
            onChangeCover: function () {
                IniteditMusic.log(this);
                var files = $("#userCoverImageInput").prop("files");
                if (files && files[0]) {
                    var input = {
                        id: "userCoverImage",
                        f: files
                    }
                    IniteditMusic.upload.music.previewImageReadURLBackground(input);
                    this.config.coverImage = files[0];
                    $("#coverImageActionContainer").slideDown();
                }
            },
            onCancelCover: function () {
                var img = $("#userCoverImage").attr("data-img");
                $("#userCoverImage").css("background-image", "url(" + img + ")");
                this.config.coverImage = null;
                $("#coverImageActionContainer").slideUp();
                if (this.config.saveCoverImageAjax != null) {
                    this.config.saveCoverImageAjax.abort();
                }
            },
            onSaveCover: function () {
                this.cover();
            }
        },
        playlist: {
            menu: {
                showMenu: function (id) {
                    $("#userPlaylistMenu_" + id).slideToggle();
                    $("#userPlaylistDeleteMenu_" + id).fadeOut();
                    $("#userPlaylistPrivacyMenu_" + id).fadeOut();
                    //$(document).mouseup(this.hideMenuShortcut.bind(id));

                },
                hideMenuShortcut: function (e) {
                    var container = $("#userPlaylistMenu_" + this);
                    if (!container.is(e.target) && container.has(e.target).length === 0)
                    {
                        container.slideUp();
                        $(document).unbind("mouseup", IniteditMusic.user.playlist.menu.hideMenuShortcut.bind(this));
                    }
                },
                showDeleteMenu: function (id) {
                    $("#userPlaylistDeleteMenu_" + id).fadeIn();
                },
                hideDeleteMenu: function (id) {
                    $("#userPlaylistDeleteMenu_" + id).fadeOut();
                },
                showPrivacyMenu: function (id) {
                    $("#userPlaylistPrivacyMenu_" + id).fadeIn();
                },
                hidePrivacyMenu: function (id) {
                    $("#userPlaylistPrivacyMenu_" + id).fadeOut();
                },
                deletePlaylist: function (playlist) {
                    var info = {
                        playlist: playlist
                    };
                    $.ajax({
                        type: "POST",
                        url: "/ajax/playlist/delete",
                        data: {info: JSON.stringify(info)},
                        dataType: "json",
                        success: function (data) {
                            IniteditMusic.recentObject.response = data;
                            if (data.code == 1)
                            {
                                $("#userPlaylistItemContainer_" + data.playlist).slideUp("fast", function () {
                                    $("#userPlaylistItemContainer_" + data.playlist).remove();
                                });
                                if ($("#userPlaylistItemContainer").length == 0)
                                {
                                    IniteditMusic.helper.reloadPage();
                                }
                            } else {
                                IniteditMusic.notification.toast.addMessageBox(1, data.message);
                            }
                        },
                        failure: function (errMsg) {
                            if (IniteditMusic.config.showError)
                                $("body").append("<div>" + errMsg + "</div>");
                            IniteditMusic.recentObject.response = errMsg;
                        },
                        error: function (errMsg) {
                            if (IniteditMusic.config.showError)
                                $("body").append("<div>" + errMsg.responseText + "</div>");
                            console.log(errMsg);
                        }
                    });
                },
                onClickDeletePlaylist: function (id) {
                    this.deletePlaylist(id);
                },
                changePrivacyPlaylist: function (playlist, privacy) {
                    var info = {
                        playlist: playlist,
                        privacy: privacy
                    };
                    IniteditMusic.log(info);
                    $.ajax({
                        type: "POST",
                        url: "/ajax/playlist/changeprivacy",
                        data: {info: JSON.stringify(info)},
                        dataType: "json",
                        success: function (data) {
                            IniteditMusic.recentObject.response = data;
                            if (data.code == 1)
                            {
                                IniteditMusic.notification.toast.addMessageBox(0, data.message);
                            } else {
                                IniteditMusic.notification.toast.addMessageBox(1, data.message);
                            }
                        },
                        failure: function (errMsg) {
                            if (IniteditMusic.config.showError)
                                $("body").append("<div>" + errMsg + "</div>");
                            IniteditMusic.recentObject.response = errMsg;
                        },
                        error: function (errMsg) {
                            if (IniteditMusic.config.showError)
                                $("body").append("<div>" + errMsg.responseText + "</div>");
                            console.log(errMsg);
                        }
                    });
                },
                onClickChangePrivacyPlaylist: function (id) {
                    var privacy = $("#userPlaylistPrivacy_" + id).val();
                    this.changePrivacyPlaylist(id, privacy);
                },
            }
        }
    },
    hObject: {
        addPlaylist: function (music) {
            if (IniteditMusic.player.audio.playlist == null) {
                IniteditMusic.player.audio.playlist = [];
            }
            var playlist = IniteditMusic.player.audio.playlist;
            var isInPLaylist = false;
            for (var i = 0; i < playlist.length; i++) {
                var m = playlist[i];
                if (m.musicid == music.musicid) {
                    isInPLaylist = true;
                }
            }
            if (!isInPLaylist) {
                IniteditMusic.player.audio.playlist.push(music);
            }
        }
    },
    page: {
        beforeUnload: function (e) {
//            if (IniteditMusic.upload.music.uploadInfoAjax.isUploadingStarted()) {
            if (!IniteditMusic.upload.music.uploadInfoAjax.isUploadingCompleted()) {
                e = e || window.event;

                // For IE and Firefox prior to version 4
                if (e) {
                    e.returnValue = 'Upload Operation is Pending';
                }

                // For others
                return 'Upload Operation is Pending';
            }
//            }
        },
        fullScreen: {
            toggleMusic: function () {
                if (IniteditMusic.player.audio.current == null) {

                    this.startFullScreenMusic();
                } else {
                    if (IniteditMusic.recentMusic.fullScreenMusic.musicid != IniteditMusic.player.audio.current.musicid) {
                        $(".musicPlayPauseButtonItem_" + IniteditMusic.player.audio.current.musicid).hide();
                        $(".backgroundBackgroundImage_" + IniteditMusic.player.audio.current.musicid).removeClass("circularRotate")

                        this.startFullScreenMusic();
                    } else {
                        IniteditMusic.player.toggle();
                    }
                }
            },
            startFullScreenMusic: function () {
                IniteditMusic.hObject.addPlaylist(IniteditMusic.recentMusic.fullScreenMusic);
                IniteditMusic.player.play(IniteditMusic.recentMusic.fullScreenMusic);
                IniteditMusic.player.updateBottomMusicProgressContainer();



            },
            onClickShowWavformMenu: function () {
                $("#waveformTypeContainer").slideToggle("fast");
            },
            setWaveformType: function (type) {
                IniteditMusic.storage.local.setWaveformType(type);
                IniteditMusic.player.waveform.regenerate($(".fullMusicDetailContainer .waveformContainer"));
                IniteditMusic.notification.toast.addMessageBox(0, "Changed");
                $("#waveformTypeContainer").slideUp("fast");
            }
        },
        home: {
            slider: {
                config: {
                    sliderInterval: null,
                    sliderStayTime: 5000,
                    current: 1
                },
                init: function () {
                    clearInterval(IniteditMusic.page.home.slider.config.sliderInterval);
                    var $sliders = $("#topHomeSlider .homeSlider > .sliderItem");
                    var sliderCount = $sliders.length;
                    $sliders.first().fadeIn().addClass("enabled");
                    IniteditMusic.page.home.slider.config.sliderInterval = setInterval(this.sliderFade, this.config.sliderStayTime);

                    var circlePoints = "";
                    for (var i = 1; i <= sliderCount; i++)
                    {
                        circlePoints += "<span class='sliderCircle' onclick='IniteditMusic.page.home.slider.sliderFadeAt(" + i + ")'></span>";
                    }
                    var circleContainer = "<div class='sliderCircleContainer'>" + circlePoints + "</div>";
                    var $circleContainer = $(circleContainer);
                    $("#topHomeSlider").append($circleContainer);
                    var marginLeft = $circleContainer.width() / 2
                    $circleContainer.css("margin-left", -marginLeft + "px");
                    $("#topHomeSlider .sliderCircle").first().addClass("enabled");

                },
                sliderFade: function () {
                    var $sliders = $("#topHomeSlider .homeSlider > .sliderItem");
                    var sliderCount = $sliders.length;

                    var i = IniteditMusic.page.home.slider.config.current++ % (sliderCount) + 1;


                    $sliders.removeClass("enabled");
                    var $sliderItem = $("#topHomeSlider .homeSlider > .sliderItem:nth-child(" + i + ")");
                    $sliderItem.addClass("enabled");
                    $("#topHomeSlider .sliderCircle").removeClass("enabled");
                    $("#topHomeSlider .sliderCircle:nth-child(" + i + ")").addClass("enabled");
                    var $otherSliderItem = $("#topHomeSlider .homeSlider > .sliderItem").not(".enabled");
                    $otherSliderItem.fadeOut(500);


                    $sliderItem.fadeIn(600, function () {

                    });

                },
                sliderFadeAt: function (i) {
                    var $sliders = $("#topHomeSlider .homeSlider > .sliderItem");
                    $sliders.removeClass("enabled");
                    $("#topHomeSlider .sliderCircle").removeClass("enabled");
                    var sliderCount = $sliders.length;
                    this.config.current = (i == 1) ? sliderCount : i - 1;
                    this.sliderFade();
                },
            }
        }
    },
    storage: {
        config: {
            keys: {
                volume: "volume",
                repeat: "repeat",
                waveformType: "waveformType"
            },
        },
        init: function () {
            if (this.local.get(this.config.keys.volume) != null)
            {
                IniteditMusic.player.config.volume.init = this.local.get(this.config.keys.volume);
            }
            if (this.local.get(this.config.keys.repeat) != null)
            {
                IniteditMusic.player.config.repeat = this.local.get(this.config.keys.repeat);
            }
            if (this.local.get(this.config.keys.waveformType) != null)
            {
                IniteditMusic.player.config.waveformType = this.local.get(this.config.keys.waveformType);
            }
        },
        local: {
            config: {
                myBool: "true"
            },
            set: function (key, value) {
                if (typeof (Storage) !== "undefined") {
                    localStorage.setItem(key, value);
                    return true;
                } else {
                    return null;
                }
            },
            get: function (key) {
                if (typeof (Storage) !== "undefined") {
                    return localStorage.getItem(key);
                } else {
                    return null;
                }
            },
            setVolume: function (val) {
                this.set(IniteditMusic.storage.config.keys.volume, val);
            },
            getVolume: function () {

                var v = this.get(IniteditMusic.storage.config.keys.volume);
                if (v != null)
                {
                    return parseInt(v * IniteditMusic.player.config.volume.max);
                } else {

                    return IniteditMusic.player.config.volume.init;
                }
            },
            setRepeat: function (val) {
                this.set(IniteditMusic.storage.config.keys.repeat, val);
            },
            getRepeat: function () {
                var v = this.get(IniteditMusic.storage.config.keys.repeat);

                if (v != null)
                {
                    return v == this.config.myBool;
                } else {
                    return IniteditMusic.player.config.repeat;
                }
            },
            setWaveformType: function (val) {
                this.set(IniteditMusic.storage.config.keys.waveformType, val);
            },
            getWaveformType: function () {
                var v = this.get(IniteditMusic.storage.config.keys.waveformType);
                if (v != null)
                {
                    return parseInt(v);
                } else {
                    return IniteditMusic.player.waveform.config.defaultType;
                }
            },
        }
    },
    search: {
        config: {
            searchDialogInterval: null,
            intervalTime: 50
        },
        onClickShowSearchDialog: function () {
            $("#searchDialogBoxContainer").fadeIn("fast", function () {
                $("#main").hide();
                $("#searchDialogInput").focus();
            });
            $(window).on("keyup", this.hideSearchDialogShortcut);

        },
        onClickHideSearchDialog: function () {
            $("#main").show();
            $("#searchDialogBoxContainer").fadeOut();
            IniteditMusic.player.updateBottomMusicProgressContainer();
        },
        hideSearchDialogShortcut: function (e) {
            if (e.keyCode == 27) {
                $(window).off("keyup", IniteditMusic.search.hideSearchDialogShortcut);
                IniteditMusic.search.onClickHideSearchDialog();
            }
        },
        searchDialogInputResult: function () {
            var search = $("#searchDialogInput").val().trim();
            if (search.length != 0) {
                $.ajax({
                    type: "POST",
                    url: "/ajax/search/getSearchDialogResult",
                    data: {search: search},
                    dataType: "json",
                    beforeSend: function (xhr) {
                        $("#searchDialogMainContentLoading").slideDown("fast");
                    },
                    success: function (data) {
                        IniteditMusic.recentObject.response = data;
                        $("#searchDialogMainContent").html(data.data);
                        IniteditMusic.helper.percessDownloadedMusicItem(data, IniteditMusic.recentMusic.searchMusic);

                        $("#searchDialogBoxContainer .stopPropagation").on("click", IniteditMusic.helper.stopPropagationAndPreventDefault);
                        $("#searchDialogBoxContainer  a").not(".linkDefault").click(IniteditMusic.helper.anchorClick);
                        $("#searchDialogBoxContainer  a").not(".linkDefault").click(function () {
                            IniteditMusic.search.onClickHideSearchDialog();
                        });
                        $("#searchDialogMainContentLoading").slideUp("fast");
                    },
                    failure: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg + "</div>");
                        IniteditMusic.recentObject.response = errMsg;
                    },
                    error: function (errMsg) {
                        if (IniteditMusic.config.showError)
                            $("body").append("<div>" + errMsg.responseText + "</div>");
                        console.log(errMsg);
                    },
                });
            }
        },
        onChangeSearchDialogInput: function () {
            if (IniteditMusic.search.config.searchDialogInterval != null) {
                clearInterval(IniteditMusic.search.config.searchDialogInterval);
            }
            IniteditMusic.search.config.searchDialogInterval = setTimeout(this.searchDialogInputResult, this.config.intervalTime);
        }
    },
    mobile: {
        menu: {
            onClickShowTopMenu: function () {
                $("#topMenuItems").html($(".settingMenu").prop("outerHTML"));
                $("#topMenuItems a").on("click", IniteditMusic.helper.anchorClick);
                $("#topMenuItems a").on("click", IniteditMusic.mobile.menu.onClickHideTopMenu);
                $("#topMenuContent").slideDown();
            },
            onClickHideTopMenu: function () {
                $("#topMenuContent").slideUp();
            },
        }
    }
};
$(document).ready(IniteditMusic.init);
$(document).ready(function () {

});