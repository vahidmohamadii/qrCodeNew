using QrCode.Application.Abstractions;

namespace QrCode.Infrastructure.Services;

internal sealed class FileStorageService : IFileStorageService
{
    private readonly string _basePath;

    public FileStorageService(string? webRootPath)
    {
        _basePath = string.IsNullOrWhiteSpace(webRootPath)
            ? Path.Combine(AppContext.BaseDirectory, "wwwroot")
            : webRootPath;
    }

    public async Task<string> SaveImageAsync(Stream content, string fileName, string folderName, CancellationToken cancellationToken = default)
    {
        var folderPath = Path.Combine(_basePath, "uploads", folderName);
        Directory.CreateDirectory(folderPath);

        var safeFileName = $"{Guid.NewGuid():N}_{Path.GetFileName(fileName)}";
        var absolutePath = Path.Combine(folderPath, safeFileName);

        await using var fileStream = File.Create(absolutePath);
        await content.CopyToAsync(fileStream, cancellationToken);

        return $"/uploads/{folderName}/{safeFileName}";
    }
}
