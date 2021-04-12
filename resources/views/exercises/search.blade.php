@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">Search for exercises</div>
                    <div class="panel-body">
                        <div class="col-md-4 no-padding margin-bottom">
                            <form action="/exercises/search" method="GET">
                                <div class="input-group">
                                    <input id="search-phrase-input" name="phrase" type="text" class="form-control" placeholder="search.." value="{{ $phrase }}">
                                    <span class="input-group-btn">
                                    <button id="search-phrase-input-button" class="btn btn-default" type="submit">
                                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                                    </button>
                                </span>
                                </div>
                            </form>
                        </div>
                        <div class="clearfix"></div>
                        <div class="table-responsive">
                            <table class="table table-bordred table-striped">
                                <thead>
                                <th>Question</th>
                                <th>Answer</th>
                                <th><span class="glyphicon glyphicon-education" aria-hidden="true"></span> %</th>
                                <th>Lesson</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                </thead>
                                <tbody>
                                @foreach($userExercises as $row)
                                    <tr>
                                        <td>
                                            {{ $row->question }}
                                        </td>
                                        <td>
                                            {{ $row->answer }}
                                        </td>
                                        <td>
                                            {{ $row->percent_of_good_answers }}
                                        </td>
                                        <td>
                                            <a href="/lessons/{{ $row->lesson_id }}">{{ $row->lesson_name }}</a>
                                        </td>
                                        <td>
                                            <a href="/exercises/{{ $row->exercise_id }}/edit" class="btn btn-info btn-lg btn-xs">
                                                <span class="glyphicon glyphicon-pencil"></span>
                                            </a>
                                        </td>
                                        <td>
                                            <button class="btn btn-danger btn-lg btn-xs" data-title="Delete"
                                                    data-toggle="modal" data-target="#delete-exercise-{{ $row->exercise_id }}">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </button>
                                        </td>
                                    </tr>
                                    <div class="modal fade" id="delete-exercise-{{ $row->exercise_id }}" tabindex="-1"
                                         role="dialog"
                                         aria-labelledby="delete" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal"
                                                            aria-hidden="true">
                                                        <span class="glyphicon glyphicon-remove"
                                                              aria-hidden="true"></span>
                                                    </button>
                                                    <h4 class="modal-title custom_align">Delete</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="alert alert-danger">
                                                        <span class="glyphicon glyphicon-warning-sign"></span>
                                                        Are you sure you want to delete?
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" form="delete-exercise-form-{{ $row->exercise_id }}" class="btn btn-success btn-lg">
                                                        <span class="glyphicon glyphicon-ok-sign"></span> Yes
                                                    </button>
                                                    <button type="button" class="btn btn-default btn-lg" data-dismiss="modal">
                                                        <span class="glyphicon glyphicon-remove"></span> No
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <form id="delete-exercise-form-{{ $row->exercise_id }}" action="/exercises/{{ $row->exercise_id }}" method="POST">
                                            <input name="_method" type="hidden" value="DELETE">
                                            {{ csrf_field() }}
                                        </form>
                                    </div>
                                @endforeach
                                </tbody>
                            </table>
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
            $("#search-phrase-input").focus();
        });
    </script>
@endpush
