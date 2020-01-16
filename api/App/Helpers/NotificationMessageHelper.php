<?php

class NotificationMessageHelper {
    const ADMIN_DETAIL_EVALUATION_URL = "admin-detail-evaluation/";
    const TEACHER_DETAIL_EVALUATION_URL = "teacher-detail-evaluation/";
    const STUDENT_DETAIL_EVALUATION_URL = "student-start-evaluation/";

    
    public static function getEvalDemarreSansSujetMsg($codeAndEvalName, $hours){
        return array(
            'title' => "Evaluation démarrant dans {$hours}H sans sujet ",
            'message' => $codeAndEvalName." ne possède pas encore de sujet et démarre dans {$hours}H "
        );
    }
    /**
     * Renvoie le titre et le message d'une notification concernant l'uploade d'un nouveau fichier
     * 
     * @param string $codeAndEvalName nom complet de l'évaluation (nom + code)
     * @param string $fileType le type de fichier uploadé. Exple : 'subject' ou 'correction'
     * @return array un tableau contenant deux clés : 'title' et 'message'
     */
    public static function getNouveauFichierMsg($codeAndEvalName, $fileType){
        if($fileType === 'subject'){
            return array(
                'title' => "Un sujet a été uploadé",
                'message' => "Un sujet a été uploadé pour l'évaluation ".$codeAndEvalName
            );
        } else if ($fileType === 'correction') {
            return array(
                'title' => "Une correction a été uploadée",
                'message' => "Une correction a été uploadée pour l'évaluation ".$codeAndEvalName
            );
        }
        
    }
    public static function getSujetEnAttenteValidationMsg($codeAndEvalName, $hours){
        return array(
            'title' => "Evaluation démarrant dans {$hours}H en attente de votre validation ",
            'message' => "Le sujet de l'évaluation ".$codeAndEvalName." attend votre validation"
        );
    }
    public static function getSujetRejeteMsg($codeAndEvalName){
        return array(
            'title' => "Sujet de l'évaluation rejeté",
            'message' => "Le sujet de l'évaluation ".$codeAndEvalName." a été rejeté"
        );
    }
    public static function getSujetValideMsg($codeAndEvalName){
        return array(
            'title' => "Sujet de l'évaluation validé",
            'message' => "Le sujet de l'évaluation ".$codeAndEvalName." a été validé"
        );
    }
    public static function getCopieNonRendueMsg($codeAndEvalName, $studentName){
        return array(
            'title' => "Copie non rendue",
            'message' => $studentName." n'a pas rendu sa copie lors de l'évaluation ".$codeAndEvalName
        );
    }
    public static function getCorrectionDisponibleMsg($codeAndEvalName){
        return array(
            'title' => "Correction disponible",
            'message' => "La correction de l'évaluation ".$codeAndEvalName." est disponible. Vous pouvez la télécharger "
        );
    }
    public static function getSujetDisponibleMsg($codeAndEvalName){
        return array(
            'title' => "Sujet disponible",
            'message' => "Le sujet de l'évaluation ".$codeAndEvalName." est disponible. Vous pouvez le télécharger "
        );
    }
}
