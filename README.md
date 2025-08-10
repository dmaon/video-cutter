# Video Cutter and Merger Web Application

## Project Overview

This project is a web-based video editing tool that enables users to select videos from their local disk, cut specific segments from them, and then merge those segments into a single consolidated video file. The application includes two primary tabs:

- **Cut Tab**: Users can select one or more videos and specify start and end points to cut segments precisely.
- **Merge Tab**: Shows a YouTube-like playlist of the cut video segments and provides a single video player with a timeline slider to play through the entire merged content as one continuous video.

All video cutting and merging operations are handled using [FFmpeg](https://ffmpeg.org), executed on the server-side via PHP's `shell_exec` function.

## Key Features

- Interactive video selection and segment cutting on the client side.
- Video processing (cutting and merging) performed securely on the server.
- Playlist-based playback of merged videos with preview and timeline navigation.
- Utilizes `ffmpeg` commands for efficient video editing without re-encoding when possible, ensuring faster processing and no loss of quality.
- Highly customizable and extendable for different video formats and user requirements.

## Suggested Improvements

- Replace shell_exec calls with PHP-FFMpeg library calls to improve security and maintainability.
- Add user authentication and file validation.
- Implement asynchronous video processing and progress updates.
- Enhance the UI for more detailed editing and playlist management.

## Why Use PHP-FFMpeg Library?

For better security, cleaner code, and improved maintainability, it is recommended to use [PHP-FFMpeg](https://github.com/PHP-FFMpeg/PHP-FFMpeg), a popular PHP object-oriented wrapper around FFmpeg binary that simplifies video processing commands and error handling. This library helps in:

- Abstracting raw shell command execution.
- Providing a fluent API for video manipulation.
- Improving error handling and debugging.
- Enhancing security by reducing risks associated with direct shell command injection.

## Prerequisites

Before running the project, ensure the following are installed and properly configured on your server:

1. **PHP** (version 7.4 or higher recommended)
2. **Web server** (Apache, Nginx, or similar with PHP support)
3. **FFmpeg binaries** available and accessible from the command line  
   Confirm installation by running:
4. Appropriate permissions on the upload and processing directories for PHP to read/write files.
5. PHP configured to allow file uploads (with suitable upload_max_filesize and post_max_size in php.ini).

## Note:

This project might have some minor bugs or quirks here and there, especially in the user interface. Since polishing the UI wasn’t the main goal, some of these may remain unfixed for a while—or maybe forever ;) Thank you for your understanding and patience!

## License

This project is provided under the MIT License. Feel free to use and modify it as per your needs.
