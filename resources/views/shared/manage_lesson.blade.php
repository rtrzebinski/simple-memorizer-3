<div class="panel panel-default">
    <div class="panel-heading">Lesson</div>
    <div class="panel-body">
        <div class="col-md-6 no-padding">
            <h4>
                <span class="glyphicon glyphicon-education" aria-hidden="true"></span>
                <a href="/lessons/{{ $lesson->id }}">{{ $lesson->name }}</a>
            </h4>
            <p>
                Visibility: {{ $lesson->visibility }} </br>
                Bidirectional: {{ $lesson->bidirectional ? 'yes' : 'no' }} </br>
                Number of exercises: {{ $lesson->all_exercises->count() }} </br>
                Number of aggregates: {{ $lesson->lessonAggregate->count() }} </br>
                Number of subscribers: {{ $lesson->subscribersWithOwnerExcluded()->count() }} </br>
                @cannot('subscribe', $lesson)
                    Percent of good answers: {{ $lesson->percentOfGoodAnswersOfUser(Auth::user()->id) }} </br>
                @endcannot
            </p>

        </div>
        <div class="col-md-6 no-padding">
            <p>
                @can('subscribe', $lesson)
                    <button type="submit" form="subscribe-and-learn" class="btn btn-primary margin-bottom">
                        <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
                        Subscribe and start
                    </button>
                    <button type="submit" form="subscribe" class="btn btn-danger margin-bottom">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                        Subscribe
                    </button>
                @endcan

                @cannot('subscribe', $lesson)
                    @can('learn', $lesson)
                        <a href="/learn/lessons/{{ $lesson->id }}" class="btn btn-primary margin-bottom" role="button">
                            <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
                            Start
                        </a>
                    @endcan
                @endcannot

                @can('unsubscribe', $lesson)
                    <button type="submit" form="unsubscribe" class="btn btn-danger margin-bottom">
                        <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
                        Unsubscribe
                    </button>
                @endcan

                @can('modify', $lesson)
                    <a href="/lessons/{{ $lesson->id }}/edit" class="btn btn-info margin-bottom" role="button">
                        <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
                        Edit
                    </a>
                @endcan

                @can('modify', $lesson)
                    <button class="btn btn-danger margin-bottom" data-title="Delete"
                            data-toggle="modal" data-target="#delete_lesson">
                        <span class="glyphicon glyphicon-trash"></span>
                        Delete
                    </button>
                @endcan

                @can('modify', $lesson)
                    <a href="/lessons/{{ $lesson->id }}/exercises/create"
                       class="btn btn-success margin-bottom" role="button">
                        <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                        Add exercise
                    </a>
                @endcan

                <a href="/lessons/{{ $lesson->id }}/exercises" class="btn btn-default margin-bottom">
                    <span class="glyphicon glyphicon-th" aria-hidden="true"></span>
                    Exercises
                </a>

                @can('modify', $lesson)
                    <a href="/lessons/aggregate/{{ $lesson->id }}" class="btn btn-default margin-bottom">
                        <span class="glyphicon glyphicon-th" aria-hidden="true"></span>
                        Aggregate
                    </a>
                @endcan

            </p>
        </div>
    </div>
    <form id="subscribe" action="/lessons/{{ $lesson->id }}/subscribe" method="POST">
        {{ csrf_field() }}
    </form>
    <form id="subscribe-and-learn" action="/lessons/{{ $lesson->id }}/subscribe-and-learn" method="POST">
        {{ csrf_field() }}
    </form>
    <form id="unsubscribe" action="/lessons/{{ $lesson->id }}/unsubscribe" method="POST">
        {{ csrf_field() }}
    </form>
</div>

<div class="modal fade" id="delete_lesson" tabindex="-1" role="dialog" aria-labelledby="delete" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
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
                <button type="submit" form="delete-lesson" type="button" class="btn btn-success">
                    <span class="glyphicon glyphicon-ok-sign"></span> Yes
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove"></span> No
                </button>
            </div>
        </div>
    </div>
    <form id="delete-lesson" action="/lessons/{{ $lesson->id }}" method="POST">
        <input name="_method" type="hidden" value="DELETE">
        {{ csrf_field() }}
    </form>
</div>
