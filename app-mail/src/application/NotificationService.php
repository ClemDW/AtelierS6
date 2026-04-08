<?php

namespace photopro\mail\application;

use photopro\mail\domain\MailerInterface;


class NotificationService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }


    public function handleGaleryEvent(array $data): bool
    {
        $eventType = $data['event_type'] ?? '';
        $destinataire = $data['destinataire'] ?? [];
        $galery = $data['galery'] ?? [];
        $nom_galery = $galery['name'] ?? '';
        $url_galery = $galery['url'] ?? '';
        $code_acces = $galery['code_acces'] ?? '';
        $email = $destinataire['email'] ?? null;
        $nom = ($destinataire['prenom'] ?? '') . ' ' . ($destinataire['nom'] ?? '');

        switch ($eventType) {
            case 'galery.modification':
                return $this->sendGaleryModifEmail($email, $nom, $nom_galery, $url_galery, $code_acces);
            case 'galery.publication':
                return $this->sendGaleryPublicationEmail($email, $nom, $nom_galery, $url_galery, $code_acces);
            case 'galery.depublication':
                return $this->sendGaleryDepublicationEmail($email, $nom, $nom_galery);
            default:
                echo "[NOTIFICATION] Type d'événement inconnu: {$eventType}\n";
                return true; // Événement inconnu : on ack pour ne pas bloquer la queue
        }
    }

    private function sendGaleryModifEmail(string $to, string $toName, string $galery_name, string $galery_url, string $code_acces = ''): bool
    {
        $subject = "Modification de galerie - {$galery_name}";
        $htmlBody = $this->buildGaleryModifEmail($toName, $galery_name, $galery_url, $code_acces);
        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $sent = $this->mailer->send($to, $subject, $htmlBody, $textBody);
        if ($sent) {
            echo "[EMAIL ENVOYÉ] Modification galerie → {$to}\n";
        }
        return $sent;
    }

    private function buildGaleryModifEmail(string $toName, string $galery_name, string $galery_url, string $code_acces = ''): string
    {
        $codeSection = $code_acces !== ''
            ? "<p>Code d'accès : <strong>{$code_acces}</strong></p>"
            : '';

        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
            </head>
            <body>
                <h2>Galerie {$galery_name} modifiée</h2>
                <p>Bonjour {$toName},</p>
                <p>La galerie a été modifiée, vous pouvez y accéder via cette url : <br>{$galery_url}</p>
                {$codeSection}
                <p>PhotoPro</p>
            </body>
            </html>
        HTML;
    }

    private function sendGaleryPublicationEmail(string $to, string $toName, string $galery_name, string $galery_url, string $code_acces = ''): bool
    {
        $subject = "Publication de galerie - {$galery_name}";
        $htmlBody = $this->buildGaleryPublicationEmail($toName, $galery_name, $galery_url, $code_acces);
        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $sent = $this->mailer->send($to, $subject, $htmlBody, $textBody);
        if ($sent) {
            echo "[EMAIL ENVOYÉ] Publication galerie → {$to}\n";
        }
        return $sent;
    }

    private function buildGaleryPublicationEmail(string $toName, string $galery_name, string $galery_url, string $code_acces = ''): string
    {
        $codeSection = $code_acces !== ''
            ? "<p>Code d'accès : <strong>{$code_acces}</strong></p>"
            : '';

        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
            </head>
            <body>
                <h2>Galerie {$galery_name} publiée</h2>
                <p>Bonjour {$toName},</p>
                <p>La galerie a été publiée, vous pouvez y accéder via cette url : <br>{$galery_url}</p>
                {$codeSection}
                <p>PhotoPro</p>
            </body>
            </html>
        HTML;
    }

    private function sendGaleryDepublicationEmail(string $to, string $toName, string $galery_name): bool
    {
        $subject = "Dépublication de galerie - {$galery_name}";
        $htmlBody = $this->buildGaleryDepublicationEmail($toName, $galery_name);
        $textBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $sent = $this->mailer->send($to, $subject, $htmlBody, $textBody);
        if ($sent) {
            echo "[EMAIL ENVOYÉ] Dépublication galerie → {$to}\n";
        }
        return $sent;
    }

    private function buildGaleryDepublicationEmail(string $toName, string $galery_name): string
    {
        return <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
            </head>
            <body>
                <h2>Galerie {$galery_name} dépubliée</h2>
                <p>Bonjour {$toName},</p>
                <p>La galerie <strong>{$galery_name}</strong> a été dépubliée et n'est plus accessible.</p>
                <p>PhotoPro</p>
            </body>
            </html>
        HTML;
    }
}
