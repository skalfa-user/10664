var buttonsActions = function () {
    var downloadUrl;
    var deletionUrl;

    this.init = function init(params) {
        downloadUrl = params['downloadUrl'];
        deletionUrl = params['deletionUrl'];

        $("#gdpr_btn_request_download").click(function () {
            location.href = downloadUrl;
        });

        $("#gdpr_btn_request_deletion").click(function () {
            location.href = deletionUrl;
        });
    }
}