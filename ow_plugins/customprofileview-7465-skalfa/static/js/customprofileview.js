$(document).ready(function () {

    if (window.customParams) {
        var photoTitle = window.customParams.photoTitle;
        var videoTitle = window.customParams.videoTitle;
        var ownerMode = window.customParams.ownerMode;
    } else {
        var photoTitle = 'Photo Albums';
        var videoTitle = 'User Video';
        var ownerMode = false;
    }

    let photoCmp = $("div.ow_dnd_widget.profile-PHOTO_CMP_UserPhotoAlbumsWidget");
    let videoCmp = $("div.ow_dnd_widget.profile-CVIDEOUPLOAD_CMP_UserVideoWidget");

    if (ownerMode === true) {
        videoCmp = $("div.ow_dnd_widget.profile-CVIDEOUPLOAD_CMP_MyVideoWidget");
    }

    let activeCmpClass = 'active_cmp';
    let inactiveCmpClass = 'inactive_cmp';
    let activeCmpTabClass = 'active_cmp_tab';

    function activeCmp() {
        if (photoCmp.length > 0) {
            if (videoCmp.length > 0) {
                photoCmp.addClass(activeCmpClass);
                videoCmp.addClass(inactiveCmpClass);
            }
        }

        if (photoCmp.length <= 0) {
            if (videoCmp.length > 0) {
                photoCmp.addClass(inactiveCmpClass);
                videoCmp.addClass(activeCmpClass);
            }
        }

        let renderTabs = '<div>';

        if (photoCmp.length > 0) {
            let activeClass = '';
            if (videoCmp.length > 0) {
                activeClass = activeCmpTabClass;
            }
            renderTabs += "<span class='photo_cmp_tab custom-tab " + activeClass +"'>" + photoTitle +"</span>";

            if (videoCmp.length > 0) {
                renderTabs += " | ";
            }

            $("div.ow_dnd_widget.profile-PHOTO_CMP_UserPhotoAlbumsWidget > .ow_dnd_configurable_component").remove();
        }

        if (videoCmp.length > 0) {
            renderTabs += "<span class='video_cmp_tab custom-tab '>" + videoTitle +"</span>";

            if (ownerMode === true) {
                $("div.ow_dnd_widget.profile-CVIDEOUPLOAD_CMP_MyVideoWidget > .ow_dnd_configurable_component").remove();
            } else {
                $("div.ow_dnd_widget.profile-CVIDEOUPLOAD_CMP_UserVideoWidget > .ow_dnd_configurable_component").remove();
            }
        }

        renderTabs +- "</div>";

        if (photoCmp.length <= 0 && videoCmp.length <= 0) {
            renderTabs = '';
        }

        $('div.place_section.top_section').prepend(renderTabs);
        $('div.place_section.top_section').removeClass('hidden');

    }

    // activeCmp();

    let photoCmpTab = $(".photo_cmp_tab");
    let videoCmpTab = $(".video_cmp_tab");

    function openCmp(cmp) {
        if (photoCmp.length > 0 && videoCmp.length > 0) {
            if (cmp === 'photo') {
                videoCmpTab.removeClass(activeCmpTabClass);
                photoCmpTab.addClass(activeCmpTabClass);

                photoCmp.removeClass(inactiveCmpClass);
                photoCmp.addClass(activeCmpClass);
                videoCmp.addClass(inactiveCmpClass);
            }

            if (cmp === 'video') {
                photoCmpTab.removeClass(activeCmpTabClass);
                videoCmpTab.addClass(activeCmpTabClass);

                videoCmp.removeClass(inactiveCmpClass);
                videoCmp.addClass(activeCmpClass);
                photoCmp.addClass(inactiveCmpClass);
            }
        }
    }

    photoCmpTab.on('click', function () {
        openCmp('photo');
    });

    videoCmpTab.on('click', function () {
        openCmp('video');
    });

});

function moveSliderNext() {
    let data_active_block = $('.custom_active_block').data('block');
    let count_photo_blocks = window.customParams.countPhotoBlocks;

    if ((data_active_block + 1) < count_photo_blocks) {
        let next = data_active_block + 1;
        $('.custom_block_' + next).removeClass('ow_hidden');
        $('.custom_block_' + data_active_block).removeClass('custom_active_block');
        $('.custom_block_' + next).addClass('custom_active_block');
        $('.custom_block_' + data_active_block).addClass('ow_hidden');
    }
}

function moveSliderPrev() {
    let data_active_block = $('.custom_active_block').data('block');

    if (data_active_block > 0) {
        let prev = data_active_block - 1;
        $('.custom_block_' + prev).removeClass('ow_hidden');
        $('.custom_block_' + data_active_block).removeClass('custom_active_block');
        $('.custom_block_' + prev).addClass('custom_active_block');
        $('.custom_block_' + data_active_block).addClass('ow_hidden');
    }
}


function fullScreenVideo(context) {
    var customVideo = document.getElementsByClassName('custom-video')[0];

    // if(customVideo.requestFullscreen){
    //     customVideo.requestFullscreen();
    // } else if (customVideo.webkitRequestFullscreen){
    //     customVideo.webkitRequestFullscreen();
    // } else if (customVideo.mozRequestFullScreen){
    //     customVideo.mozRequestFullScreen();
    // } else if (customVideo.msRequestFullscreen){
    //     customVideo.msRequestFullscreen();
    // }

}

$('.wrapper-video').click(function () {
    $("#avatarImage").addClass('ow_hidden');
    $("#avatarVideo").removeClass('ow_hidden');

    $(".playpause-play").fadeOut();
    $(".custom-video-play").get(0).play();
});

$('.wrapper-video-play').click(function () {
    if($(this).children(".custom-video-play").get(0).paused) {
        $(this).children(".playpause-play").fadeOut();
        $(".custom-video-play").get(0).play();
    } else{
        $(this).children(".playpause-play").fadeIn();
        $(".custom-video-play").get(0).pause();
    }
});

function setOnAvatarImage(originalUrl) {
    var avatarImage = document.getElementById('avatarImage');
    avatarImage.style.backgroundImage = 'url("' + originalUrl + '"';

    $("#avatarImage").removeClass('ow_hidden');
    $("#avatarVideo").addClass('ow_hidden');
}