@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Edit exercise</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8 margin-bottom">
                                <form method="POST" action="/exercises/{{ $exercise->id }}">
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="PUT">
                                    <a href="/exercises/{{ $exercise->id }}" class="btn btn-default margin-bottom">
                                        <span class="glyphicon glyphicon-list" aria-hidden="true"></span>
                                        Browse exercises
                                    </a>
                                    <hr>
                                    <div class="form-group {{ $errors->has('question') ? 'has-error' : false }}">
                                        <label>Question</label>
                                        <textarea name="question" class="form-control" rows="4">{{ $exercise->question }}</textarea>
                                    </div>
                                    <div class="form-group {{ $errors->has('answer') ? 'has-error' : false }}">
                                        <label>Correct answer</label>
                                        <textarea name="answer" class="form-control" rows="4">{{ $exercise->answer }}</textarea>
                                    </div>
                                    <button type="submit" class="btn btn-default">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
