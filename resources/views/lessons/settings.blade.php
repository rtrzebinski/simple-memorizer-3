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
                                <form method="POST" content="" action="/lessons/{{ $lesson->id }}/settings">
                                    {{ csrf_field() }}
                                    <input name="_method" type="hidden" value="PUT">

                                    <div class="form-group">
                                        <label>Threshold</label>
                                        <input name="threshold" type="text" class="form-control" value="{{ $lesson->threshold(Auth::user()->id) }}">
                                        <span class="help-block">
                                            Maximum percent of good answers of exercise, that will allow exercise to be served in learning mode. </br>
                                            Set to 90 if you only want to learn exercises with percent of good answers between 1 and 90, and skip best known. </br>
                                            Set to 50 to focus only on exercises with more bad than good answers.
                                        </span>
                                    </div>

                                    <div class="form-group">
                                        <label>Bidirectional</label>
                                        <select name="bidirectional" class="form-control">
                                            <option @if($lesson->isBidirectional(Auth::user()->id)) selected @endif value="1">Yes</option>
                                            <option @if(!$lesson->isBidirectional(Auth::user()->id)) selected @endif value="0">No</option>
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
