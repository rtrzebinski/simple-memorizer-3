@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Edit lesson</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8 margin-bottom">
                                <form method="POST" content="" action="/lessons/{{ $lesson->id }}">
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="PUT">

                                    <div class="form-group">
                                        <label>Name</label>
                                        <input name="name" type="text" class="form-control" value="{{ $lesson->name }}">
                                        <span class="help-block">
                                            Descriptive name of the lesson.
                                        </span>
                                    </div>

                                    <div class="form-group">
                                        <label>Visibility</label>
                                        <select name="visibility" class="form-control">
                                            <option @if($lesson->visibility == 'public') selected @endif value="public">Public</option>
                                            <option @if($lesson->visibility == 'private') selected @endif value="private">Private</option>
                                        </select>
                                        <span class="help-block">
                                            Public lessons can be subscribed by other users, but only you can modify them. </br>
                                            Private lessons are only visible for you.
                                        </span>
                                    </div>

                                    <div class="form-group">
                                        <label>Bidirectional</label>
                                        <select name="bidirectional" class="form-control">
                                            <option @if($lesson->bidirectional) selected @endif value="1">Yes</option>
                                            <option @if(!$lesson->bidirectional) selected @endif value="0">No</option>
                                        </select>
                                        <span class="help-block">
                                            Bidirectional lesson will serve exercises in 2 versions -> regular + with question and answer reversed.
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
