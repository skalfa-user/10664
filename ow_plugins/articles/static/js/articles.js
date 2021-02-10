function makeFeaturedOrNot(id, featuredIndex) {
    let featured = 1;

    if (featuredIndex == 1) {
        featured = 0;
    }

    $.ajax({
        url: window.articlesParams.updateFeaturedUrl,
        type: "POST",
        data: {
            'id': id,
            'featured': featured
        },
        dataType: "json",
        cache: false,
        success: function (data) {
            if (data['success']) {
                location.reload();
            }
        },
    })

}

function deleteArticle(id) {

    $.ajax({
        url: window.articlesParams.deleteArticleUrl,
        type: "POST",
        data: {
            'id': id
        },
        dataType: "json",
        cache: false,
        success: function (data) {
            if (data['success']) {
                location.reload();
            }
        },
    })
}

function showNewImageField() {
    document.getElementById('current_image_field').style.display = 'none';
    document.getElementById('new_image_field').style.display = 'block';
}