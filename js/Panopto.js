var Panopto = {

    video_urls: [],

    signature: '',

    playVideo: function (sid) {
        console.log('playVideo ' + sid);
        // $('#xpan_waiter_modal').show();
        var $modal = $('#xpan_modal_player');
        $modal.modal('show');
        // TODO: generic src from config
        var $iframe = '<iframe src="https://fh-muenster.cloud.panopto.eu/Panopto/Pages/Embed.aspx?id=' + sid + '" width="720" height="405" style="padding: 0px; border: 1px solid #464646;" frameborder="0" allowfullscreen allow="autoplay"></iframe>';
        $modal.find('div#xpan_video_container').html($iframe);
        // $modal.find('h4.modal-title').html('test_title'); // TODO: set modal title
        $('#xoct_waiter_modal').show();

        $modal.on('hidden', function() { // bootstrap 2.3.2
            $video = $('video')[0];
            if(typeof $video != 'undefined') {
                $video.pause();
            }
            $iframe = $('iframe');
            if (typeof $iframe != 'undefined') {
                $iframe.attr('src', '');
            }
        });

        $modal.on('hidden.bs.modal', function() {  // bootstrap 3
            $video = $('video')[0];
            if(typeof $video != 'undefined') {
                $video.pause();
            }
            $iframe = $('iframe');
            if (typeof $iframe != 'undefined') {
                $iframe.attr('src', '');
            }
        });
    }

};