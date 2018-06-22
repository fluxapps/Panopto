var Panopto = {

    video_urls: [],

    signature: '',

    playVideo: function (sid) {
        console.log('playVideo ' + sid);
        // $('#xpan_waiter_modal').show();
        var $modal = $('#xpan_modal_player');
        $modal.modal('show');
        // $.get({
        //     url: this.ajax_base_url,
        //     data: {
        //         "cmd": "fillModalPlayer",
        //         "sid": sid
        //     }
        // }).always(function(response) {
        //     response_object = JSON.parse(response);
        //     $modal.find('div#xpan_video_container').html(response_object.html);
        //     $modal.find('h4.modal-title').html(response_object.video_title);
        //     $('#xoct_waiter_modal').show();
        //     if (typeof VimpObserver != 'undefined') {
        //         VimpObserver.init(mid, response_object.time_ranges);
        //     }
        //
        //     $modal.on('hidden', function() { // bootstrap 2.3.2
        //         $video = $('video')[0];
        //         if(typeof $video != 'undefined') {
        //             $video.pause();
        //         }
        //         $iframe = $('iframe');
        //         if (typeof $iframe != 'undefined') {
        //             $iframe.attr('src', '');
        //         }
        //     });
        //
        //     $modal.on('hidden.bs.modal', function() {  // bootstrap 3
        //         $video = $('video')[0];
        //         if(typeof $video != 'undefined') {
        //             $video.pause();
        //         }
        //         $iframe = $('iframe');
        //         if (typeof $iframe != 'undefined') {
        //             $iframe.attr('src', '');
        //         }
        //     });
        // });
        var $iframe = '<iframe src="https://fh-muenster.cloud.panopto.eu/Panopto/Pages/Embed.aspx?id=' + sid + '" width="720" height="405" style="padding: 0px; border: 1px solid #464646;" frameborder="0" allowfullscreen allow="autoplay"></iframe>';
        $modal.find('div#xpan_video_container').html($iframe);
        $modal.find('h4.modal-title').html('test_title');
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