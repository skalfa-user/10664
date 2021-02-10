$(document).ready(function () {
    $('#mail_floatbox').click(function () {
        var floatbox = new OW_FloatBox({
            $title: OW.getLanguageText("gdpr", "gdpr_send_message_label"),
            $contents: $('#mail_floatbox_content'),
            width: '30%'
        });
    });
});