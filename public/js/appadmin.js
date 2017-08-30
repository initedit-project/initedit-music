var IniteditMusicAdmin = {
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
    init: function () {
//        .not(".linkDefault")
        IniteditMusicAdmin.config.csrf = $("body").attr("data-csrf");
        IniteditMusicAdmin.config.siteName = $("body").attr("data-site");
        $("a").not(".linkDefault").click(IniteditMusicAdmin.helper.anchorClick);
        IniteditMusicAdmin.helper.reloadPage();
        IniteditMusicAdmin.notification.init();
        IniteditMusicAdmin.helper.init();
    },
    helper: {
        init: function () {
            window.onpopstate = function (e) {
                IniteditMusicAdmin.helper.reloadPage();
            };
        },
        anchorClick: function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            IniteditMusicAdmin.helper.loadNewPage(href)
        },
        loadNewPage: function (url) {
            url = url.substr(6)
            if (url[0] == "/")
            {
                url = url.substr(1);
            }
            IniteditMusicAdmin.recentObject.recentAjax = $.ajax({
                type: "POST",
                url: "/admin/ajax/" + url,
                data: {info: JSON.stringify({url: url})},
                dataType: "json",
                beforeSend: function (xhr) {
                    $("#mainProgressBar").show();
                },
                success: function (data) {
                    IniteditMusicAdmin.recentObject.response = data;
                    IniteditMusicAdmin.helper.processDownloadedPage(data, url);
                    $("#mainProgressBar").css("width", 100 + "%").delay(500).hide("fast", function () {
                        $("#mainProgressBar").css("width", 0 + "%")
                    });
                },
                failure: function (errMsg) {
                    if (IniteditMusicAdmin.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusicAdmin.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusicAdmin.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                },
                xhr: function () {
                    // get the native XmlHttpRequest object
                    var xhr = $.ajaxSettings.xhr();
                    // set the onprogress event handler
                    xhr.upload.onprogress = IniteditMusicAdmin.helper.updateProgress;
                    xhr.onload = function () {

                        console.log('DONE!')
                    };
                    // return the customized object
                    return xhr;
                }
            });
        },
        reloadPage: function () {
            var href = window.location.pathname + window.location.search;
            IniteditMusicAdmin.helper.loadNewPage(href);
        },
        updateProgress: function (oEvent) {
            if (oEvent.lengthComputable) {
                var percentComplete = oEvent.loaded / oEvent.total * 100;
                console.log(percentComplete);
                $("#mainProgressBar").css("width", percentComplete * 0.67 + "%");
            } else {
                console.log("percentComplete");
                // Unable to compute progress information since the total size is unknown
            }
        },
        processDownloadedPage: function (data, url) {
            if (IniteditMusicAdmin.config.showSiteNameInTitle) {
                $("title").text(data.title + " - " + IniteditMusicAdmin.config.siteName);
            } else {
                $("title").text(data.title);
            }
            $("#main").html(data.data);
            $("#main a").not(".linkDefault").click(IniteditMusicAdmin.helper.anchorClick);
            if (url != "") {
                window.history.pushState("", "", "/admin/" + url);
            } else {
                window.history.pushState("", "", "/admin/");
            }

            window.scrollTo(0, 0);
        },
        preventDefault: function (e) {
            e.preventDefault();
        },
    },
    log: function (msg) {
        if (window.console)
            console.log(msg);
    },
    account: {
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
                url: "/admin/ajax/login/validate",
                data: {info: JSON.stringify(info)},
                dataType: "json",
                success: function (data) {
                    IniteditMusicAdmin.recentObject.response = data;
                    if (data.code == 1)
                    {
                        IniteditMusicAdmin.notification.toast.addMessageBox(0, data.message);
                        $(".settingMenuLoggedIn").show();
                        $(".settingMenuLogIn").hide();
                        $("#settingMenuUserName").attr("href", data.profileurl);
                        setTimeout(function () {
                            IniteditMusicAdmin.helper.loadNewPage(data.nextPage);
                        }, 2000);
                    } else {
                        $("#wrongpassword").show();
                        $("#wrongpassword").html(data.message);
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusicAdmin.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusicAdmin.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusicAdmin.config.showError)
                        $("body").append("<div>" + errMsg.responseText + "</div>");
                    console.log(errMsg);
                }
            });
        }
        ,
        logout: function (reload) {
            $.ajax({
                type: "POST",
                url: "/admin/ajax/login/logout",
                data: {csrf: IniteditMusicAdmin.config.csrf},
                dataType: "json",
                success: function (data) {
                    IniteditMusicAdmin.recentObject.response = data;
                    if (data.code == 1)
                    {
                        IniteditMusicAdmin.notification.toast.addMessageBox(0, data.message);
                        $(".settingMenuLoggedIn").hide();
                        $(".settingMenuLogIn").show();
                        if (reload)
                        {
                            IniteditMusicAdmin.helper.reloadPage();
                        }
                    } else {
                        IniteditMusicAdmin.notification.toast.addMessageBox(1, data.message);
                    }
                },
                failure: function (errMsg) {
                    if (IniteditMusicAdmin.config.showError)
                        $("body").append("<div>" + errMsg + "</div>");
                    IniteditMusicAdmin.recentObject.response = errMsg;
                },
                error: function (errMsg) {
                    if (IniteditMusicAdmin.config.showError)
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
                    <li class="close" onclick="IniteditMusicAdmin.notification.toast.closeMessageBox(\'' + id + '\')">x</li>\
                </ul>\
            </div>';
                $("#rightMessageBox").prepend(boxMsg);
                var msgid = {boxid: id};
                var f = IniteditMusicAdmin.notification.toast.showMessageBox.bind(msgid);
                setTimeout(f, 0);
            },
            showMessageBox: function () {
                IniteditMusicAdmin.log(this);
                $("#" + this.boxid).css("opacity", "1");
                f = IniteditMusicAdmin.notification.toast.hideMessageBox.bind(this);
                setTimeout(f, 4000);
            },
            hideMessageBox: function () {
                $("#" + this.boxid).css("opacity", "0");
                f = IniteditMusicAdmin.notification.toast.removeMessageBox.bind(this);
                setTimeout(f, 500);
            },
            removeMessageBox: function () {
                $("#" + this.boxid).remove();
            },
            closeMessageBox: function (id) {
                var msgid = {boxid: id};
                f = IniteditMusicAdmin.notification.toast.hideMessageBox.bind(msgid);
                setTimeout(f, 0);
            }
        }
    },
    upload: {
        music: {
            config: {
                isUploading: false
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
                    if (IniteditMusicAdmin.upload.music.edit.image != null) {
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
                        formData.append('image', IniteditMusicAdmin.upload.music.edit.image);
                    }
                    IniteditMusicAdmin.upload.music.edit.editAjax = $.ajax({
                        url: '/admin/ajax/upload/editmusic',
                        data: formData,
                        contentType: false,
                        processData: false,
                        type: "POST",
                        dataType: "json",
                        success: function (data) {
                            if (data.code == 1) {
                                IniteditMusicAdmin.notification.toast.addMessageBox(0, data.message);
                            } else {
                                IniteditMusicAdmin.notification.toast.addMessageBox(1, data.message);
                            }
                            IniteditMusicAdmin.log(data);
                        },
                        failure: function (errMsg) {
                            if (IniteditMusicAdmin.config.showError)
                                $("body").append("<div>" + errMsg + "</div>");
                            IniteditMusicAdmin.recentObject.response = errMsg;
                        },
                        error: function (errMsg) {
                            if (IniteditMusicAdmin.config.showError)
                                $("body").append("<div>" + errMsg.responseText + "</div>");
                            console.log(errMsg);
                        }
                    });
                },
                onChangeImageFile: function () {

                    var files = $("#editImageInput").prop("files");
                    if (files != undefined && files[0] != undefined) {

                        IniteditMusicAdmin.upload.music.edit.image = files[0];
                        var info = {
                            id: "editImageContainer",
                            f: files
                        }
                        IniteditMusicAdmin.upload.music.previewImageReadURLBackground(info);
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
                        url: '/admin/ajax/upload/deletemusic',
//                        data: {info: JSON.stringify({extra: info})},
                        data: {extra: JSON.stringify(info)},
                        dataType: "json",
                        success: function (data) {
                            IniteditMusicAdmin.recentObject.response = data;
                            if (data.code == 1) {
                                IniteditMusicAdmin.notification.toast.addMessageBox(0, data.message);
                                if (data.nextpage != undefined)
                                {
                                    setTimeout(function () {
                                        IniteditMusicAdmin.helper.loadNewPage(data.nextpage);
                                    }, 1000);
                                }
                            } else {
                                IniteditMusicAdmin.notification.toast.addMessageBox(1, data.message);
                            }
                            IniteditMusicAdmin.log(data);
                        },
                        failure: function (errMsg) {
                            if (IniteditMusicAdmin.config.showError)
                                $("body").append("<div>" + errMsg + "</div>");
                            IniteditMusicAdmin.recentObject.response = errMsg;
                        },
                        error: function (errMsg) {
                            if (IniteditMusicAdmin.config.showError)
                                $("body").append("<div>" + errMsg.responseText + "</div>");
                            console.log(errMsg);
                        }
                    });
                }
            }
        }
    },
    page: {
        music: {
            serach: {
                onKeyPressSearch: function (e) {
                    if (e.keyCode == 13) {
                        //Enter pressed
                        var search = $("#adminSearchInput").val();
                        var searchURI = "/admin/music/search/?s=" + search;
                        IniteditMusicAdmin.helper.loadNewPage(searchURI);
                    }
                }
            }
        },
        setting: {
            general: {
                changeTitle: function () {
                    var name = $("#adminSiteName").val();
                    var info = {
                        name: name,
                        csrf: IniteditMusicAdmin.config.csrf
                    };
                    $.ajax({
                        type: "POST",
                        url: '/admin/ajax/setting/updateSiteName',
                        data: {extra: JSON.stringify(info)},
                        dataType: "json",
                        success: function (data) {
                            IniteditMusicAdmin.recentObject.response = data;
                            if (data.code == 1) {
                                IniteditMusicAdmin.notification.toast.addMessageBox(0, data.message);
                                if (data.refresh != undefined)
                                {
                                    setTimeout(function () {
//                                        
                                        window.location.href = window.location.href;
                                    }, 1000);
                                }
                            } else {
                                IniteditMusicAdmin.notification.toast.addMessageBox(1, data.message);
                            }
                            IniteditMusicAdmin.log(data);
                        },
                        failure: function (errMsg) {
                            if (IniteditMusicAdmin.config.showError)
                                $("body").append("<div>" + errMsg + "</div>");
                            IniteditMusicAdmin.recentObject.response = errMsg;
                        },
                        error: function (errMsg) {
                            if (IniteditMusicAdmin.config.showError)
                                $("body").append("<div>" + errMsg.responseText + "</div>");
                            console.log(errMsg);
                        }
                    });
                },
                changeHelpEmail: function () {
                    var email = $("#adminHelpEmail").val();
                    var info = {
                        email: email,
                        csrf: IniteditMusicAdmin.config.csrf
                    };
                    $.ajax({
                        type: "POST",
                        url: '/admin/ajax/setting/updateHelpEmail',
                        data: {extra: JSON.stringify(info)},
                        dataType: "json",
                        success: function (data) {
                            IniteditMusicAdmin.recentObject.response = data;
                            if (data.code == 1) {
                                IniteditMusicAdmin.notification.toast.addMessageBox(0, data.message);
                                if (data.refresh != undefined)
                                {
                                    setTimeout(function () {
                                        window.location.href = window.location.href;
                                    }, 1000);
                                }
                            } else {
                                IniteditMusicAdmin.notification.toast.addMessageBox(1, data.message);
                            }
                            IniteditMusicAdmin.log(data);
                        },
                        failure: function (errMsg) {
                            if (IniteditMusicAdmin.config.showError)
                                $("body").append("<div>" + errMsg + "</div>");
                            IniteditMusicAdmin.recentObject.response = errMsg;
                        },
                        error: function (errMsg) {
                            if (IniteditMusicAdmin.config.showError)
                                $("body").append("<div>" + errMsg.responseText + "</div>");
                            console.log(errMsg);
                        }
                    });
                }
            }
        }
    },
    svg: {
        polarToCartesian: function (centerX, centerY, radius, angleInDegrees) {
            var angleInRadians = (angleInDegrees - 90) * Math.PI / 180.0;
            return {
                x: centerX + (radius * Math.cos(angleInRadians)),
                y: centerY + (radius * Math.sin(angleInRadians))
            };
        }
        ,
        describeArc: function (x, y, radius, startAngle, endAngle) {

            var start = polarToCartesian(x, y, radius, endAngle);
            var end = polarToCartesian(x, y, radius, startAngle);
            var arcSweep = endAngle - startAngle <= 180 ? "0" : "1";
            var d = [
                "M", start.x, start.y,
                "A", radius, radius, 0, arcSweep, 0, end.x, end.y
            ].join(" ");
            return d;
        },
        drawCircle: function () {

        }
    }
};
$(document).ready(IniteditMusicAdmin.init);