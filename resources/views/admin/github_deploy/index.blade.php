@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0"><i class="fab fa-github mr-2"></i>GitHub Deploy</h1>
        @if($isRepo)
            <div>
                <form action="{{ route('admin.github_deploy.fetch') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sync-alt mr-1"></i> Check for Updates
                    </button>
                </form>
            </div>
        @endif
    </div>

    @include('admin.settings._flash')

    @if(session('git_output'))
        <div class="card shadow-sm mb-3">
            <div class="card-header font-weight-bold"><i class="fas fa-terminal mr-1"></i> Git output</div>
            <div class="card-body bg-dark p-2">
                <pre class="text-light small mb-0" style="max-height:300px;overflow:auto;white-space:pre-wrap">{{ session('git_output') }}</pre>
            </div>
        </div>
    @endif

    @if(!$isRepo)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            The project folder is not a git repository, or <code>git</code> is not available to the web server.
        </div>
    @else
        <div class="row">
            {{-- Repository info + pull --}}
            <div class="col-lg-5 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header font-weight-bold">Repository</div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-3">
                            <tr><th class="pl-0" style="width:110px">Remote</th><td class="text-break">{{ $info['remote'] ?: '—' }}</td></tr>
                            <tr><th class="pl-0">Branch</th><td><span class="badge badge-info">{{ $info['branch'] ?: 'detached HEAD' }}</span></td></tr>
                            <tr><th class="pl-0">Current</th><td><code>{{ substr($info['head'], 0, 7) }}</code></td></tr>
                            <tr>
                                <th class="pl-0">Incoming</th>
                                <td>
                                    @if(count($info['incoming']))
                                        <span class="badge badge-warning">{{ count($info['incoming']) }} new commit(s)</span>
                                    @else
                                        <span class="badge badge-success">Up to date</span>
                                        <small class="text-muted d-block">as of last check</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th class="pl-0">Local changes</th>
                                <td>
                                    @if(count($info['changes']))
                                        <a href="#" data-toggle="collapse" data-target="#localChanges">
                                            {{ count($info['changes']) }} modified file(s)
                                        </a>
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        @if(count($info['changes']))
                            <div id="localChanges" class="collapse mb-3">
                                <pre class="bg-light border p-2 small mb-0" style="max-height:200px;overflow:auto">{{ implode("\n", $info['changes']) }}</pre>
                            </div>
                            <div class="alert alert-warning small py-2">
                                The server has uncommitted changes. A pull fails if GitHub changed the same files.
                            </div>
                        @endif

                        <form action="{{ route('admin.github_deploy.pull') }}" method="POST"
                              onsubmit="return confirm('Pull latest code from origin/{{ $info['branch'] }}?');">
                            @csrf
                            <div class="custom-control custom-checkbox mb-2">
                                <input type="checkbox" class="custom-control-input" id="clear_cache" name="clear_cache" value="1" checked>
                                <label class="custom-control-label" for="clear_cache">Clear caches after pull (<code>optimize:clear</code>)</label>
                            </div>
                            <button type="submit" class="btn btn-success" @disabled(!$info['branch'])>
                                <i class="fas fa-cloud-download-alt mr-1"></i> Pull from GitHub
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Incoming commits --}}
            <div class="col-lg-7 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header font-weight-bold">
                        Incoming commits <small class="text-muted">(on GitHub, not yet pulled)</small>
                    </div>
                    <div class="card-body p-0" style="max-height:340px;overflow:auto">
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                @forelse($info['incoming'] as $c)
                                    <tr>
                                        <td><code>{{ $c['short'] }}</code></td>
                                        <td>{{ $c['message'] }}</td>
                                        <td class="text-nowrap small text-muted">{{ $c['author'] }}</td>
                                        <td class="text-nowrap small text-muted">{{ \Carbon\Carbon::parse($c['date'])->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr><td class="text-center text-muted py-4">Nothing new. Click <strong>Check for Updates</strong> to fetch from GitHub.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Commit history --}}
        <div class="card shadow-sm mb-3">
            <div class="card-header font-weight-bold">Commit history <small class="text-muted">(last 30 on this server)</small></div>
            <div class="card-body p-0" style="max-height:420px;overflow:auto">
                <table class="table table-hover table-striped table-sm mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Commit</th>
                            <th>Message</th>
                            <th>Author</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($info['commits'] as $c)
                            <tr @class(['table-success' => $c['hash'] === $info['head']])>
                                <td><code>{{ $c['short'] }}</code></td>
                                <td>{{ $c['message'] }}</td>
                                <td class="text-nowrap">{{ $c['author'] }}</td>
                                <td class="text-nowrap">{{ \Carbon\Carbon::parse($c['date'])->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No commits found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Pull history (from git reflog) --}}
    @if($isRepo)
    <div class="card shadow-sm">
        <div class="card-header font-weight-bold">
            Pull history <small class="text-muted">(from git reflog, last {{ count($pulls) }})</small>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Result</th>
                        <th>Change</th>
                        <th class="text-center">Commits</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pulls as $i => $pull)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="text-nowrap">
                                {{ \Carbon\Carbon::parse($pull['date'])->format('d M Y, h:i A') }}
                                <small class="d-block text-muted">{{ \Carbon\Carbon::parse($pull['date'])->diffForHumans() }}</small>
                            </td>
                            <td>{{ $pull['message'] }}</td>
                            <td class="text-nowrap">
                                @if($pull['before'])
                                    <code>{{ substr($pull['before'], 0, 7) }}</code> → <code>{{ substr($pull['after'], 0, 7) }}</code>
                                @else
                                    <code>{{ substr($pull['after'], 0, 7) }}</code>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(count($pull['commits']))
                                    <button type="button" class="btn btn-info btn-sm" data-toggle="collapse" data-target="#pull-{{ $i }}">
                                        {{ count($pull['commits']) }} <i class="fas fa-chevron-down ml-1"></i>
                                    </button>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                        </tr>
                        @if(count($pull['commits']))
                            <tr id="pull-{{ $i }}" class="collapse">
                                <td colspan="5" class="bg-light p-0">
                                    <table class="table table-sm mb-0">
                                        @foreach($pull['commits'] as $c)
                                            <tr>
                                                <td style="width:90px"><code>{{ $c['short'] }}</code></td>
                                                <td>{{ $c['message'] }}</td>
                                                <td class="text-nowrap small text-muted">{{ $c['author'] }}</td>
                                                <td class="text-nowrap small text-muted">{{ \Carbon\Carbon::parse($c['date'])->format('d M Y, h:i A') }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No pulls recorded in the reflog yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
