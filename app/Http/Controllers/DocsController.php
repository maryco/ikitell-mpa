<?php

namespace App\Http\Controllers;

use App\Models\Entities\Device;
use App\Models\Entities\DeviceDashboard;
use App\Models\Repositories\DeviceRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocsController extends Controller
{
    /**
     * @var DeviceRepositoryInterface
     */
    protected $deviceRepo;

    /**
     * DocsController constructor.
     * @param DeviceRepositoryInterface $deviceRepo
     */
    public function __construct(DeviceRepositoryInterface $deviceRepo)
    {
        $this->deviceRepo = $deviceRepo;
    }

    /**
     * Show about page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function about(Request $request)
    {
        $mockDevice = $this->deviceRepo->makeMock([
            'reported_at' => now()->subDays(1)->getTimestamp(),
            'in_alert' => false,
            'in_suspend' => false
        ]);

        return view(
            'docs.about',
            [
                'mockDashboard' => (new DeviceDashboard($mockDevice))->setDemo(true),
                'panelState' => $this->getPanelState($request),
            ]
        );
    }

    /**
     * Show terms page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function terms()
    {
        return view('docs.terms');
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getPanelState(Request $request)
    {
        $open = $request->get('open', null);

        // Default states.
        $panels =  [
            'usage_step01' => true,
            'usage_step02' => false,
            'usage_step03' => false,
            'usage_step04' => false,
            'how_reset' => true,
            'how_alert' => false,
            'specs' => false,
        ];

        if (!$open) {
            return $panels;
        }

        foreach (array_keys($panels) as $panelId) {
            if ($panelId === $open) {
                $panels[$panelId] = true;
            }
        }

        return $panels;
    }
}
