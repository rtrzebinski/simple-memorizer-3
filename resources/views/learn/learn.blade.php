@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-default">
                    <div class="panel-heading">Learning</div>
                    <div class="panel-body">

                        <div class="row">
                            <div class="col-md-8 col-md-offset-2">
                                <h4>
                                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                                    {{ $lesson->name }}
                                </h4>
                                <a href="/lessons/{{ $lesson->id }}" class="btn btn-default margin-bottom">
                                    <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                    Lesson
                                </a>
                                @can('modify', $exercise)
                                    <button type="submit" form="update-exercise-form"
                                            class="btn btn-default margin-bottom">
                                        <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
                                        Save changes
                                    </button>
                                @endcan
                                <button id="show_answer_button" class="btn btn-default margin-bottom">
                                    <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                                    Show answer
                                </button>
                                <a href="/learn/lessons/{{ $lesson->id }}?previous_exercise_id={{ $exercise->id }}">
                                    <button class="btn btn-default margin-bottom" id="next-button">
                                        <span class="glyphicon glyphicon-step-forward" aria-hidden="true"></span>
                                        Next
                                    </button>
                                </a>
                            </div>
                        </div>

                        </br>

                        <div class="row">
                            <form method="POST" id="update-exercise-form" action="/learn/exercises/{{ $exercise->id }}/{{ $lesson->id }}">
                                {{ csrf_field() }}
                                <input name="_method" type="hidden" value="PUT">
                                <div class="col-md-8 col-md-offset-2 margin-bottom">
                                    <label>
                                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                                        Question
                                    </label>
                                    <textarea name="question" class="form-control" rows="{{ substr_count( $exercise->question, "\n" ) + 1 }}">{{ $exercise->question }}</textarea>
                                </div>
                                <div class="clearfix"></div>
                                <div id="answer_input" class="col-md-8 col-md-offset-2 margin-bottom hidden">
                                    </br>
                                    <label>
                                        <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>
                                        Correct answer
                                    </label>
                                    <textarea name="answer" class="form-control" rows="{{ substr_count( $exercise->answer, "\n" ) + 1 }}">{{ $exercise->answer }}</textarea>
                                </div>
                            </form>
                        </div>

                        </br>

                        <div class="row">
                            <div class="col-md-8 col-md-offset-2">
                                <button type="submit" form="handle-good-answer-form" id="good-answer-button" class="btn btn-default btn-success margin-bottom">
                                    <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                                    Ask less
                                </button>
                                <button type="submit" form="handle-bad-answer-form" id="bad-answer-button" class="btn btn-default btn-danger margin-bottom">
                                    <span class="glyphicon glyphicon-thumbs-down" aria-hidden="true"></span>
                                    Ask more
                                </button>
                            </div>
                            <form id="handle-good-answer-form"
                                  action="/learn/handle-good-answer/exercises/{{ $exercise->id }}/{{ $lesson->id }}"
                                  method="POST">
                                {{ csrf_field() }}
                            </form>
                            <form id="handle-bad-answer-form"
                                  action="/learn/handle-bad-answer/exercises/{{ $exercise->id }}/{{ $lesson->id }}"
                                  method="POST">
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" language="JavaScript">
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
            if (ev.which == 38) {
                // up arrow: 38
                $("#show_answer_button").click();
            }
            if (ev.which == 37) {
                // left arrow: 37
                $("#good-answer-button").click();
            }
            if (ev.which == 40) {
                // down arrow: 40
                $("#bad-answer-button").click();
            }
            if (ev.which == 39) {
                // right arrow: 39
                $("#next-button").click();
            }
            // quick learning - use space bar to show answer / skip to next question
            if (ev.which == 32) {
                // space: 32
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

    </script>
@endpush
