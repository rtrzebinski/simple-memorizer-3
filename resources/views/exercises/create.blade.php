@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">

                @include('shared.manage_lesson')

                <div class="panel panel-default">
                    <div class="panel-heading">Add exercise</div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8 margin-bottom">
                                <form method="POST" action="/lessons/{{ $userLesson->lesson_id }}/exercises">
                                    {{ csrf_field() }}
                                    <div class="form-group {{ $errors->has('question') ? 'has-error' : false }}">
                                        <label>Question</label>
                                        <textarea id="textarea_question" name="question" class="form-control" rows="4"></textarea>
                                    </div>
                                    <div class="form-group {{ $errors->has('answer') ? 'has-error' : false }}">
                                        <label>Correct answer</label>
                                        <textarea id="textarea_answer" name="answer" class="form-control" rows="4"></textarea>
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
@push('scripts')
    <script type="text/javascript" language="JavaScript">
        $(document).ready(function () {
            $("#textarea_question").focus();
        });
    </script>
@endpush
