<?php

namespace ReportKit\Laravel\Legacy\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Scaffold a new report stack (definition + repo + service + controller + blade stubs).
 *
 * Does NOT modify existing reports.
 */
class MakeReportCommand extends Command
{
    protected $name = 'reportkit:make';

    protected $description = 'Scaffold a new ReportKit report (stubs only; does not change existing reports)';

    public function fire()
    {
        $name = $this->argument('name');
        $studly = preg_replace('/[^A-Za-z0-9]/', '', $name);
        $slug = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $studly));
        $route = $this->option('route') ?: ('admin/' . $slug . '-report');
        $preset = $this->option('preset') ?: 'hybrid';
        $layout = $this->option('layout') ?: 'layouts.master';
        $flagsOpt = $this->option('flags');
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $flags = $this->resolveFlags($preset, $flagsOpt);

        $this->info("Scaffold ReportKit report: {$studly} (preset={$preset}, route={$route})");
        $this->line('Flags: ' . implode(',', array_keys(array_filter($flags))));
        $this->line('Layout: ' . $layout);
        $this->line('');

        $stubDir = dirname(dirname(__DIR__)) . '/resources/stubs';
        $replacements = array(
            '{{Studly}}' => $studly,
            '{{slug}}' => $slug,
            '{{route}}' => $route,
            '{{title}}' => preg_replace('/([a-z])([A-Z])/', '$1 $2', $studly) . ' Report',
            '{{preset}}' => $preset,
            '{{layout}}' => $layout,
            '{{flags_php}}' => $this->exportFlagsPhp($flags),
        );

        $map = array(
            "app/Reports/{$studly}Report.php" => 'report.definition.stub',
            "app/Repositories/Reports/{$studly}ReportRepository.php" => 'report.repository.stub',
            "app/Services/Reports/{$studly}ReportService.php" => 'report.service.stub',
            "app/controllers/admin/Reports/{$studly}ReportController.php" => 'report.controller.stub',
            "app/views/admin/reports/{$slug}.blade.php" => 'report.blade.stub',
            "public/js/reports/{$slug}.js" => 'report.js.stub',
            "app/tests/Reports/{$studly}ReportTest.php" => 'report.test.stub',
        );

        foreach ($map as $relative => $stubName) {
            if ($dryRun) {
                $this->line('[dry-run] ' . $relative);
                continue;
            }

            $target = base_path($relative);
            $stubFile = $stubDir . '/' . $stubName;

            if (file_exists($target) && !$force) {
                $this->comment("Skip existing: {$relative}");
                continue;
            }

            if (!file_exists($stubFile)) {
                $this->error("Missing stub: {$stubName}");
                continue;
            }

            $dir = dirname($target);

            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }

            file_put_contents($target, strtr(file_get_contents($stubFile), $replacements));
            $this->info("Wrote {$relative}");
        }

        if ($dryRun) {
            $this->info('Dry run only — no files written.');
            return;
        }

        $this->line('');
        $this->info('Next: fill repository SQL, add routes in your domain route file, then:');
        $this->line('  composer dump-autoload');
        $this->comment('Suggested routes (global controller class — L4.1 classmap):');
        $this->line("  Route::get('{$route}', '{$studly}ReportController@index');");
        if (!empty($flags['datatables'])) {
            $this->line("  Route::get('{$route}/data', '{$studly}ReportController@data');");
        }
        $this->comment('Existing reports were not modified.');
    }

    /**
     * @param string $preset
     * @param string|null $flagsOpt
     * @return array
     */
    protected function resolveFlags($preset, $flagsOpt)
    {
        $defaults = array(
            'datatable' => array(
                'datatables' => true,
                'sync' => true,
                'async_prepare' => false,
                'kpi' => true,
                'excel' => true,
                'csv' => true,
                'pdf' => false,
                'email' => false,
            ),
            'prepare' => array(
                'datatables' => false,
                'sync' => false,
                'async_prepare' => true,
                'kpi' => true,
                'excel' => true,
                'csv' => true,
                'pdf' => true,
                'email' => true,
            ),
            'hybrid' => array(
                'datatables' => true,
                'sync' => true,
                'async_prepare' => true,
                'kpi' => true,
                'excel' => true,
                'csv' => true,
                'pdf' => true,
                'email' => false,
            ),
        );

        $flags = isset($defaults[$preset]) ? $defaults[$preset] : $defaults['hybrid'];

        if ($flagsOpt) {
            $requested = array_filter(array_map('trim', explode(',', $flagsOpt)));
            $all = array('datatables', 'sync', 'async_prepare', 'kpi', 'excel', 'csv', 'pdf', 'email', 'print', 'howto');
            foreach ($all as $key) {
                $flags[$key] = in_array($key, $requested, true);
            }
        }

        return $flags;
    }

    /**
     * @param array $flags
     * @return string
     */
    protected function exportFlagsPhp(array $flags)
    {
        $lines = array();
        foreach ($flags as $key => $on) {
            $lines[] = "            '{$key}' => " . ($on ? 'true' : 'false') . ',';
        }

        return implode("\n", $lines);
    }

    protected function getArguments()
    {
        return array(
            array('name', InputArgument::REQUIRED, 'Studly report name, e.g. Demo'),
        );
    }

    protected function getOptions()
    {
        return array(
            array('route', null, InputOption::VALUE_OPTIONAL, 'Route prefix', null),
            array('preset', null, InputOption::VALUE_OPTIONAL, 'datatable|prepare|hybrid', 'hybrid'),
            array('layout', null, InputOption::VALUE_OPTIONAL, 'Blade layout to @extends', 'layouts.master'),
            array('flags', null, InputOption::VALUE_OPTIONAL, 'Comma list overriding preset', null),
            array('force', null, InputOption::VALUE_NONE, 'Overwrite existing files'),
            array('dry-run', null, InputOption::VALUE_NONE, 'Print paths only'),
        );
    }
}
