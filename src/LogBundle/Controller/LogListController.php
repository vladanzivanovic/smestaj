<?php

namespace LogBundle\Controller;

use LogBundle\Model\LogList;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogListController extends AbstractController
{
    private string $logDir;

    public function __construct(
      string $logDir
    ) {

        $this->logDir = $logDir;
    }

    /**
     * @Route(path="/", name="log_list_view")
     */
    public function logListAction(Request $request): Response
    {
        $logs = (new LogList())->getLogList($this->logDir);
        return $this->render('@Log/listView.html.twig', [
            'logs' => $logs
        ]);
    }

}
