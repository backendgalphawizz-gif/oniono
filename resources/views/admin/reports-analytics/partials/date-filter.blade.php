<x-panel-toolbar>
    <x-slot:filter>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-from">From</label>
            <input type="date" name="from" id="filter-from" class="form-control" value="{{ $from->format('Y-m-d') }}">
        </div>
        <div class="col-md-4 col-lg-3 filter-field">
            <label class="form-label" for="filter-to">To</label>
            <input type="date" name="to" id="filter-to" class="form-control" value="{{ $to->format('Y-m-d') }}">
        </div>
    </x-slot:filter>
</x-panel-toolbar>
