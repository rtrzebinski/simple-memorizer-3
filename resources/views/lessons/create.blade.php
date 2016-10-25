@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                <div class="panel panel-default">
                    <div class="panel-heading">Create new lesson</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8 margin-bottom">
                                <a href="/home" class="btn btn-default margin-bottom">
                                    <span class="glyphicon glyphicon-th" aria-hidden="true"></span>
                                    Browse lessons
                                </a>
                                <hr>
                                <form method="POST" content="" action="/lessons">
                                    {{ csrf_field() }}
                                    <div class="form-group {{ $errors->has('name') ? 'has-error' : false }}">
                                        <label>Name</label>
                                        <input name="name" type="text" class="form-control" placeholder="">
                                        <span class="help-block">
                                            Descriptive name of the lesson.
                                        </span>
                                    </div>
                                    <div class="form-group">
                                        <label>Visibility</label>
                                        <select name="visibility" class="form-control">
                                            <option value="public">Public</option>
                                            <option value="private">Private</option>
                                        </select>
                                        <span class="help-block">
                                            Public lessons can be subscribed by other users, but only you can modify them. </br>
                                            Private lessons are only visible for you.
                                        </span>
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
