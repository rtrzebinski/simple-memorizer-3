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
                                <button class="btn btn-default margin-bottom">
                                    <span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span>
                                    Save changes
                                </button>
                                <button class="btn btn-default margin-bottom">
                                    <span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                                    Display answer
                                </button>
                            </div>
                        </div>

                        </br>

                        <div class="row">
                            <div class="col-md-8 col-md-offset-2 margin-bottom">
                                <label>
                                    <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                                    Question
                                </label>
                                <textarea class="form-control" rows="4">encode</textarea>
                            </div>
                            <div class="clearfix"></div>
                            </br>
                            <div class="col-md-8 col-md-offset-2 margin-bottom">
                                <label>
                                    <span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>
                                    Correct answer
                                </label>
                                <textarea class="form-control" rows="4">zakodować</textarea>
                            </div>
                        </div>

                        </br>

                        <div class="row">
                            <div class="col-md-8 col-md-offset-2">
                                <button class="btn btn-default btn-success btn-lg margin-bottom">
                                    <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                                    I know the answer
                                </button>
                                <button class="btn btn-default btn-danger btn-lg margin-bottom">
                                    <span class="glyphicon glyphicon-thumbs-down" aria-hidden="true"></span>
                                    I don't know the answer
                                </button>
                                <button class="btn btn-default">
                                    <span class="glyphicon glyphicon-step-forward" aria-hidden="true"></span>
                                    Next
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
