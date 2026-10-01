<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Process;

class GithubDeployController extends Controller
{
    public function index()
    {
        $isRepo = $this->git(['rev-parse', '--is-inside-work-tree'])['ok'];

        $info = [
            'branch'   => null,
            'head'     => null,
            'remote'   => null,
            'changes'  => [],
            'incoming' => [],
            'commits'  => [],
        ];

        if ($isRepo) {
            $info['branch']  = $this->currentBranch();
            $info['head']    = trim($this->git(['rev-parse', 'HEAD'])['output']);
            $info['remote']  = $this->maskUrl(trim($this->git(['remote', 'get-url', 'origin'])['output']));
            $info['changes'] = array_values(array_filter(explode("\n", $this->git(['status', '--porcelain'])['output'])));
            $info['commits'] = $this->commits(['-n', '30', 'HEAD']);

            // Commits on origin not yet pulled (based on the last fetch)
            if ($info['branch'] && $this->git(['rev-parse', '--verify', '--quiet', "origin/{$info['branch']}"])['ok']) {
                $info['incoming'] = $this->commits(["HEAD..origin/{$info['branch']}"]);
            }
        }

        $pulls = $isRepo ? $this->pullHistory() : [];

        return view('admin.github_deploy.index', compact('isRepo', 'info', 'pulls'));
    }

    public function fetch(Request $request)
    {
        $result = $this->git(['fetch', 'origin', '--prune'], 120);

        return redirect()->route('admin.github_deploy.index')
            ->with($result['ok'] ? 'success' : 'error', $result['ok']
                ? 'Fetched latest changes from GitHub.'
                : 'Fetch failed. See the git output below.')
            ->with('git_output', $this->maskUrl("$ git fetch origin --prune\n" . $result['output']));
    }

    public function pull(Request $request)
    {
        $branch = $this->currentBranch();
        if (!$branch) {
            return back()->with('error', 'Cannot pull: repository is in a detached HEAD state.');
        }

        $before = trim($this->git(['rev-parse', 'HEAD'])['output']);

        // --ff-only: never create merge commits or overwrite local work on the server
        $result = $this->git(['pull', '--ff-only', 'origin', $branch], 300);

        $after = trim($this->git(['rev-parse', 'HEAD'])['output']);
        $count = 0;
        if ($result['ok'] && $before && $after && $before !== $after) {
            $count = (int) trim($this->git(['rev-list', '--count', "{$before}..{$after}"])['output']);
        }

        if ($result['ok'] && $request->boolean('clear_cache')) {
            try {
                Artisan::call('optimize:clear');
                $result['output'] .= "\n\n$ php artisan optimize:clear\n" . Artisan::output();
            } catch (\Throwable $e) {
                $result['output'] .= "\n\noptimize:clear failed: " . $e->getMessage();
            }
        }

        $output = $this->maskUrl("$ git pull --ff-only origin {$branch}\n" . $result['output']);

        if (!$result['ok']) {
            return redirect()->route('admin.github_deploy.index')
                ->with('error', 'Pull failed. See the git output below.')
                ->with('git_output', $output);
        }

        return redirect()->route('admin.github_deploy.index')
            ->with('success', $count > 0
                ? "Pulled {$count} new commit(s) from origin/{$branch}."
                : 'Already up to date.')
            ->with('git_output', $output);
    }

    /**
     * Run a git command in the project root. Returns ['ok' => bool, 'output' => string].
     */
    private function git(array $args, int $timeout = 30): array
    {
        $cmd = array_merge(['git', '-c', 'safe.directory=' . str_replace('\\', '/', base_path())], $args);

        try {
            $process = Process::path(base_path())
                ->timeout($timeout)
                ->env([
                    'GIT_TERMINAL_PROMPT' => '0',   // never hang waiting for credentials
                    'HOME' => getenv('HOME') ?: (getenv('USERPROFILE') ?: base_path()),
                ])
                ->run($cmd);

            return [
                'ok'     => $process->successful(),
                'output' => trim($process->output() . "\n" . $process->errorOutput()),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'output' => $e->getMessage()];
        }
    }

    private function currentBranch(): ?string
    {
        $branch = trim($this->git(['rev-parse', '--abbrev-ref', 'HEAD'])['output']);
        return ($branch === '' || $branch === 'HEAD') ? null : $branch;
    }

    private function commits(array $range): array
    {
        $sep = "\x1f";
        $result = $this->git(array_merge(
            ['log', '--date=iso-strict', '--pretty=format:%H%x1f%h%x1f%an%x1f%ad%x1f%s'],
            $range
        ));

        if (!$result['ok'] || $result['output'] === '') {
            return [];
        }

        return collect(explode("\n", $result['output']))
            ->map(function ($line) use ($sep) {
                $parts = explode($sep, $line, 5);
                if (count($parts) < 5) {
                    return null;
                }
                return [
                    'hash'    => $parts[0],
                    'short'   => $parts[1],
                    'author'  => $parts[2],
                    'date'    => $parts[3],
                    'message' => $parts[4],
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Pull history from git's reflog: every "pull: ..." entry on HEAD, with the
     * commits it brought in (previous reflog entry .. this entry).
     */
    private function pullHistory(int $limit = 20): array
    {
        $result = $this->git([
            'log', '-g', '-n', '500', '--date=iso-strict',
            '--pretty=format:%H%x1f%gd%x1f%gn%x1f%gs', 'HEAD',
        ]);

        if (!$result['ok'] || $result['output'] === '') {
            return [];
        }

        $entries = array_map(fn ($line) => explode("\x1f", $line, 4), explode("\n", $result['output']));
        $pulls = [];

        foreach ($entries as $i => $entry) {
            if (count($entry) < 4 || !str_starts_with($entry[3], 'pull')) {
                continue;
            }

            $after  = $entry[0];
            $before = $entries[$i + 1][0] ?? null;

            $pulls[] = [
                'date'    => trim(substr($entry[1], 5), '{}'),   // HEAD@{2025-09-06T10:39:12+06:00}
                'by'      => $entry[2],
                'message' => $entry[3],
                'before'  => $before,
                'after'   => $after,
                'commits' => ($before && $before !== $after) ? $this->commits(["{$before}..{$after}"]) : [],
            ];

            if (count($pulls) >= $limit) {
                break;
            }
        }

        return $pulls;
    }

    /** Hide tokens embedded in remote URLs, e.g. https://user:token@github.com/... */
    private function maskUrl(string $text): string
    {
        return preg_replace('#(https?://)[^@/\s]+@#', '$1***@', $text);
    }
}
