$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#show_answer_button").click(function (event) {
        $("#answer_input").removeClass("hidden");
        $("#show_answer_button").hide();
    });

    $("body").keyup(function (ev) {
        if ($(ev.target).is('input, textarea, select')) {
            return;
        }
        if (ev.which === 38) {
            // up arrow: 38
            $("#show_answer_button").click();
        }
        if (ev.which === 37) {
            // left arrow: 37
            $("#bad-answer-button").click();
        }
        if (ev.which === 39) {
            // right arrow: 39
            $("#good-answer-button").click();
        }
        // quick learning - use space bar to show answer / skip to next question
        if (ev.which === 32) {
            // space bar: 32
            if ($("#answer_input").is(':hidden')) {
                // space bar when answer is not shown - show answer
                $("#show_answer_button").click();
            } else {
                // space bar when answer is shown - next
                $("#next-button").click();
            }
        }
    });
});
