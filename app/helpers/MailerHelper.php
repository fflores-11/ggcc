<?php
/**
 * Helper para envío de correos usando SMTP configurado por comunidad
 * Requiere: composer require swiftmailer/swiftmailer
 */

class MailerHelper {
    private ConfiguracionSMTP $smtpModel;
    private array $mailerCache = [];

    public function __construct() {
        $this->smtpModel = new ConfiguracionSMTP();
    }

    /**
     * Obtiene el mailer configurado para una comunidad
     * @param int $comunidadId
     * @return Swift_Mailer|null
     */
    private function getMailer(int $comunidadId): ?\Swift_Mailer {
        // Retornar desde caché si existe
        if (isset($this->mailerCache[$comunidadId])) {
            return $this->mailerCache[$comunidadId];
        }

        // Obtener configuración SMTP
        $config = $this->smtpModel->getByComunidad($comunidadId);

        if (!$config) {
            return null;
        }

        try {
            // Crear transporte SMTP
            $encryption = $config['encryption'] === 'none' ? null : $config['encryption'];
            $transport = (new Swift_SmtpTransport($config['host'], $config['port'], $encryption))
                ->setUsername($config['username'])
                ->setPassword($config['password']);

            // Crear mailer
            $mailer = new Swift_Mailer($transport);
            
            // Guardar en caché
            $this->mailerCache[$comunidadId] = $mailer;
            
            return $mailer;
        } catch (Exception $e) {
            error_log('Error al crear mailer SMTP: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Envía un correo usando la configuración SMTP de la comunidad
     * @param int $comunidadId
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $body
     * @param bool $isHtml
     * @return array ['success' => bool, 'message' => string]
     */
    public function send(int $comunidadId, string $toEmail, string $toName, string $subject, string $body, bool $isHtml = true): array {
        $config = $this->smtpModel->getByComunidad($comunidadId);

        if (!$config) {
            return [
                'success' => false,
                'message' => 'No hay configuración SMTP para esta comunidad'
            ];
        }

        $mailer = $this->getMailer($comunidadId);

        if (!$mailer) {
            return [
                'success' => false,
                'message' => 'No se pudo inicializar el servidor SMTP'
            ];
        }

        try {
            // Crear mensaje
            $message = (new Swift_Message($subject))
                ->setFrom([$config['from_email'] => $config['from_name']])
                ->setTo([$toEmail => $toName]);

            if ($isHtml) {
                $message->setBody($body, 'text/html');
            } else {
                $message->setBody($body);
            }

            // Enviar
            $result = $mailer->send($message);

            if ($result > 0) {
                return [
                    'success' => true,
                    'message' => 'Correo enviado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se pudo enviar el correo'
                ];
            }
        } catch (Exception $e) {
            error_log('Error al enviar correo: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Envía correos masivos a múltiples destinatarios
     * @param int $comunidadId
     * @param array $destinatarios Array de ['email' => ..., 'nombre' => ...]
     * @param string $subject
     * @param string $bodyTemplate
     * @param bool $isHtml
     * @return array ['exitosos' => int, 'fallidos' => int, 'errores' => array]
     */
    public function sendBulk(int $comunidadId, array $destinatarios, string $subject, string $bodyTemplate, bool $isHtml = true): array {
        $exitosos = 0;
        $fallidos = 0;
        $errores = [];

        foreach ($destinatarios as $destinatario) {
            $email = $destinatario['email'] ?? '';
            $nombre = $destinatario['nombre'] ?? '';

            // Reemplazar variables en el template
            $body = $bodyTemplate;
            if (isset($destinatario['variables'])) {
                foreach ($destinatario['variables'] as $key => $value) {
                    $body = str_replace('{' . $key . '}', $value, $body);
                }
            }

            $result = $this->send($comunidadId, $email, $nombre, $subject, $body, $isHtml);

            if ($result['success']) {
                $exitosos++;
            } else {
                $fallidos++;
                $errores[] = [
                    'email' => $email,
                    'error' => $result['message']
                ];
            }
        }

        return [
            'exitosos' => $exitosos,
            'fallidos' => $fallidos,
            'errores' => $errores
        ];
    }

    /**
     * Verifica si hay configuración SMTP para una comunidad
     * @param int $comunidadId
     * @return bool
     */
    public function hasConfig(int $comunidadId): bool {
        return $this->smtpModel->getByComunidad($comunidadId) !== null;
    }
}
