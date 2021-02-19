var Panopto = {

    base_url: '',

    playVideo: function (sid, is_playlist) {
        console.log('playVideo ' + sid);
        console.log('is playlist ' + is_playlist);
        var $modal = $('#xpan_modal_player');
        $modal.modal('show');
        var $iframe = '<iframe src="' + Panopto.base_url + '/Panopto/Pages/Embed.aspx?' + (is_playlist ? 'p' : '') + 'id=' + sid + '" width="720" height="405" style="padding: 0px; border: 1px solid #464646;" frameborder="0" allowfullscreen allow="autoplay"></iframe>';
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
