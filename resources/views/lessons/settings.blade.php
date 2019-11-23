@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Settings</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8 margin-bottom">
                                <form method="POST" content="" action="/lessons/{{ $userLesson->lesson_id }}/settings">
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="PUT">

                                    <div class="form-group">
                                        <label>Bidirectional</label>
                                        <select name="bidirectional" class="form-control">
                                            <option @if($userLesson->is_bidirectional) selected @endif value="1">Yes</option>
                                            <option @if(!$userLesson->is_bidirectional) selected @endif value="0">No</option>
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
