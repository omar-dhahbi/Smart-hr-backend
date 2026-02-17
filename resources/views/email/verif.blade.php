<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Activation de votre compte</title>
</head>

<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border-radius: 5px; overflow: hidden;">
                    <tr>
                        <td align="center" style="background-color:  #3F51B5;; color: #ffffff; padding: 30px 20px;">
                            <h1 style="margin: 0;">Verification de votre compte</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px 40px; color: #333;">
                            <p>Suite à votre candidature et entretien, nous vous invitons à activer votre compte.</p>
                            <p><strong>Email:</strong> {{ $details['email'] }}</p>
                            <p><strong>Password:</strong> {{ $details['password'] }}</p>
                            <div style="margin-top: 30px; text-align: center;">
                                <a href="http://localhost:4200/Verif/{{ $details['id'] }}"
                                    style="display: inline-block; padding: 12px 24px; background-color:  #3F51B5;; color: white; text-decoration: none; border-radius: 5px;">
                                    Activer votre compte
                                </a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding: 20px; font-size: 12px; color: #aaa;">
                            &copy; 2025.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
