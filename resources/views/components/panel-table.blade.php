<div class="card card-soft">
    @if (!empty($title))
        <div class="card-header-brand d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h6 mb-0">{{ $title }}</h2>
            @if(isset($actions))<div>{{ $actions }}</div>@endif
        </div>
    @endif
    <div class="card-body {{ !empty($title) ? 'pt-3' : '' }}">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                {{ $slot }}
            </table>
        </div>
        @if (isset($paginator) && $paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
            <div class="mt-3 pt-3 border-top panel-pagination">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <p class="small text-muted mb-0">
                        @if ($paginator->total() > 0)
                            Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
                        @else
                            No records
                        @endif
                    </p>
                    @if ($paginator->hasPages())
                        {{ $paginator->withQueryString()->onEachSide(1)->links() }}
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
