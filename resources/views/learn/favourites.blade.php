@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-default">
                    <div class="panel-heading">Learning your <u>favourite lessons</u></div>
                    <div class="panel-body">

                        @if($userExercise)

                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <h4>
                                        <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                        <a href="/lessons/{{ $userExercise->lesson_id }}">{{ $userExercise->lesson_name }}</a>
                                    </h4>
                                    {{--check if user can modify lesson without db query--}}
                                    @if($canEditExercise)
                                        <a href="{{ $editExerciseUrl }}">
                                            <button class="btn btn-default margin-bottom">
                                                <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                                                Edit
                                            </button>
                                        </a>
                                    @endif
                                    <button id="show_answer_button" class="btn btn-default margin-bottom">
                                        <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                                        Show answer
                                    </button>
                                    <a href="/learn/favourites?previous_exercise_id={{ $userExercise->exercise_id }}">
                                        <button class="btn btn-default margin-bottom" id="next-button">
                                            <span class="glyphicon glyphicon-step-forward" aria-hidden="true"></span>
                                            Next
                                        </button>
                                    </a>
                                </div>
                            </div>

                            <br/>

                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <label>
                                        <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                                        Question
                                    </label>
                                    <div class="well well-sm">{{ $userExercise->question }}</div>
                                </div>
                                <div class="clearfix"></div>
                                <div id="answer_input" class="col-md-8 col-md-offset-2 hidden">
                                    <label>
                                        <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>
                                        Correct answer
                                    </label>
                                    <div class="well well-sm">{{ $userExercise->answer }}</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8 col-md-offset-2">
                                    <button type="submit" form="handle-good-answer-form" id="good-answer-button" class="btn btn-default btn-success margin-bottom">
                                        <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                                        Good answer
                                    </button>
                                    <button type="submit" form="handle-bad-answer-form" id="bad-answer-button" class="btn btn-default btn-danger margin-bottom">
                                        <span class="glyphicon glyphicon-thumbs-down" aria-hidden="true"></span>
                                        Bad answer
                                    </button>
                                </div>
                                <form id="handle-good-answer-form"
                                      action="/learn/favourites"
                                      method="POST">
                                    <input type="hidden" name="previous_exercise_id" value="{{ $userExercise->exercise_id }}">
                                    <input type="hidden" name="answer" value="good">
                                    {{ csrf_field() }}
                                </form>
                                <form id="handle-bad-answer-form"
                                      action="/learn/favourites"
                                      method="POST">
                                    <input type="hidden" name="previous_exercise_id" value="{{ $userExercise->exercise_id }}">
                                    <input type="hidden" name="answer" value="bad">
                                    {{ csrf_field() }}
                                </form>
                            </div>
                    </div>
                </div>

                @else

                    <div class="row">
                        <div class="col-md-8 col-md-offset-2">
                            <h4>
                                <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                                Learning exercises of your favourite lessons
                            </h4>

                            Well done! You've answered correctly to all questions today. Please come back tomorrow.

                        </div>
                    </div>

                @endif

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="/js/learn.js"></script>
@endpush
