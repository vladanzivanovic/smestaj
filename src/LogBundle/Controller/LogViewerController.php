<?php

namespace LogBundle\Controller;

use LogBundle\Model\LogView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LogViewerController extends Controller
{
    private string $logDir;

    public function __construct(
        string $logDir
    ) {

        $this->logDir = $logDir;
    }

    /**
     * @Route("logs/viewer", name="log_viewer")
     * @param Request $request
     * @return Response
     */
    public function logViewAction(Request $request): Response
    {
        $log = $request->query->get('log');
        $delete = filter_var($request->query->get('delete'), FILTER_VALIDATE_BOOLEAN);

        $logfile = $this->logDir."/$log";

        // Check that the requested file is within the log directory:
        // we probe one character ahead to make sure we validate the full directory name
        // and not just directories that starts with the same substring
        $canonicalLogDir = realpath($this->logDir);
        $canonicalLogFile = realpath($logfile);
        if(substr($canonicalLogFile, 0, strlen($canonicalLogDir)+1) !== $canonicalLogDir.DIRECTORY_SEPARATOR){
            throw $this->createAccessDeniedException();
        }

        if($delete) {
            unlink($logfile);
            return $this->redirectToRoute('log_list_view');
        }

        if (file_exists($logfile)) {
            $log = file_get_contents($logfile);
            $context['log'] = LogView::logToArray($log);
        } else {
            $context['noLog'] = true;
        }
        return $this->render('@Log/logView.html.twig', $context);
    }
}
