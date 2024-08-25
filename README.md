# PHPvision

![image](https://github.com/user-attachments/assets/03cbab1d-fd52-45c0-9cda-8c87df7e6c39)

**PHPVision** is a lightweight, server-side analytics tool built in PHP. It tracks user visits without using cookies, making it privacy-friendly and simple to implement. Ideal for small projects or those who prefer minimalistic solutions, PHPVision provides basic insights into visitor statistics directly on your website.

## Features

- **Cookieless Tracking:** No need for cookies, making it GDPR-compliant.
- **Server-Side Analytics:** Tracks user activity directly on your server.
- **Simple Implementation:** Easy to set up with minimal configuration.
- **Lightweight:** Minimal impact on server performance.
- **Privacy-Friendly:** No personal data collection.

## Installation

### Requirements

1. **A way of hosting PHP:** To actually run the web pages.

2. **A MySQL database:** To store events.

### Steps

1. **Download and Extract:**
   - Download the latest version from releases and extract it to your server.

2. **Update configuration**
   - Open `phpVisionConfig.php` in any text editor and change the database credentials to your MySQL database.

3. **Include PHPVision:**
   - Add the following line of code to the top of your PHP pages you want to track:
     ```php
     <?php require "phpVisionApi.php";
           phpVision_auto_register(); ?>
     ```

4. **View Analytics:**
   - Access your analytics by navigating to `phpVision.php` in your browser. Or where you put it.

## License

PHPVision is open-source software licensed under the [MIT License](LICENSE).
