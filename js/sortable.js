PanoptoSorter = {

    base_link: '',

    fixHelper: function (e, ui) {
        ui.children().each(function () {
            $(this).width($(this).width());
        });
        return ui;
    },

    reSort: function (e, ui) {
        //xoctWaiter.show();
        var order = [];
        $("div.ilTableOuter table tbody tr").each(function () {
            order.push($(this).attr('id'));
        });

        var ajax_url = PanoptoSorter.base_link + '&cmd=reorder';
        $.ajax({
            url: ajax_url,
            type: "POST",
            data: {
                "ids": order
            },
            success: function (data) {
                console.log(data)
            }
        });
    },

    init: function (base_link) {
        PanoptoSorter.base_link = base_link;

        $("div.ilTableOuter table tbody").sortable({
            helper: PanoptoSorter.fixHelper,
            //items: '.xvmpSortable',
            stop: PanoptoSorter.reSort
        }).disableSelection();
    }
};