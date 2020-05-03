@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Add many exercises</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8 margin-bottom">
                                <p>
                                    Provide each exercise in separated line using "question - answer" format. Invalid lines (without "-") will be ignored.
                                </p>
                                <form method="POST" action="/lessons/{{ $userLesson->lesson_id }}/exercises-many">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <label>Exercises</label>
                                        <textarea name="exercises" class="form-control" rows="20"></textarea>
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
