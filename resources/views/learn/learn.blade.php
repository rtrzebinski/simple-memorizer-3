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
                                <a href="/home" class="btn btn-default margin-bottom">
                                    <span class="glyphicon glyphicon-th" aria-hidden="true"></span>
                                    Browse lessons
                                </a>
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
                            </div>
                        </div>

                        </br>

                        <div class="row">
                            <form method="POST" id="update-exercise-form" action="/learn/exercises/{{ $exercise->id }}">
                                {{ csrf_field() }}
                                <input name="_method" type="hidden" value="PUT">
                                <div class="col-md-8 col-md-offset-2 margin-bottom">
                                    <label>
                                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                                        Question
                                    </label>
                                    <textarea name="question" class="form-control"
                                              rows="4">{{ $exercise->question }}</textarea>
                                </div>
                                <div class="clearfix"></div>
                                <div id="answer_input" class="col-md-8 col-md-offset-2 margin-bottom hidden">
                                    </br>
                                    <label>
                                        <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>
                                        Correct answer
                                    </label>
                                    <textarea name="answer" class="form-control"
                                              rows="4">{{ $exercise->answer }}</textarea>
                                </div>
                            </form>
                        </div>

                        </br>

                        <div class="row">
                            <div class="col-md-8 col-md-offset-2">
                                <button type="submit" form="handle-good-answer-form" id="good-answer-button"
                                        class="btn btn-default btn-success btn-lg margin-bottom">
                                    <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                                    I know the answer
                                </button>
                                <button id="bad-answer-button" class="btn btn-default btn-danger btn-lg margin-bottom">
                                    <span class="glyphicon glyphicon-thumbs-down" aria-hidden="true"></span>
                                    I don't know the answer
                                </button>
                                <a href="/learn/lessons/{{ $lesson->id }}?previous_exercise_id={{ $exercise->id }}">
                                    <button class="btn btn-default margin-bottom" id="next-button">
                                        <span class="glyphicon glyphicon-step-forward" aria-hidden="true"></span>
                                        Next
                                    </button>
                                </a>
                            </div>
                            <form id="handle-good-answer-form"
                                  action="/learn/handle-good-answer/exercises/{{ $exercise->id }}"
                                  method="POST">
                                {{ csrf_field() }}
                            </form>
                            <form id="handle-bad-answer-form"
                                  action="/learn/handle-bad-answer/exercises/{{ $exercise->id }}"
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

        $("#bad-answer-button").click(function (event) {
            event.preventDefault();
            $.post("/learn/handle-bad-answer/exercises/{{ $exercise->id }}", function (data) {
                $("#answer_input").removeClass("hidden");
                $("#good-answer-button").hide();
                $("#bad-answer-button").hide();
                $("#show_answer_button").hide();
                $("#next-button").addClass('btn-primary');
            });
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
        });
    });
</script>
@endpush
