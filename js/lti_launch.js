var PanoptoLTI = {

    launchUrl: '',

    launchData: [],

    signature: '',

    launch: function() {
        var data = {"oauth_signature": this.signature};
        $.each(this.launchData, function(v, i) {
           data[i] = v;
        });
        $.ajax({
            url: this.launchUrl,
            type: "POST",
            data: data
        }).always(function(response) {
            console.log(response);
        });
    }
};