using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using QrCode.Application.Abstractions;
using QrCode.Application.Catalog;

namespace QrCode.Api.Controllers;

[ApiController]
[Route("api/products")]
public sealed class ProductsController : ControllerBase
{
    private readonly IProductService _productService;
    private readonly IConfiguration _configuration;
    private readonly IFileStorageService _fileStorageService;

    public ProductsController(IProductService productService, IConfiguration configuration, IFileStorageService fileStorageService)
    {
        _productService = productService;
        _configuration = configuration;
        _fileStorageService = fileStorageService;
    }

    [HttpGet]
    [AllowAnonymous]
    public async Task<ActionResult<IReadOnlyList<ProductDto>>> GetAll([FromQuery] string? search, [FromQuery] int? categoryId, CancellationToken cancellationToken)
    {
        return Ok(await _productService.GetAllAsync(search, categoryId, cancellationToken));
    }

    [HttpGet("{id:int}")]
    [AllowAnonymous]
    public async Task<ActionResult<ProductDto>> GetById(int id, CancellationToken cancellationToken)
    {
        var result = await _productService.GetByIdAsync(id, cancellationToken);
        return result is null ? NotFound() : Ok(result);
    }

    [HttpGet("by-slug/{slug}")]
    [AllowAnonymous]
    public async Task<ActionResult<PublicProductDto>> GetBySlug(string slug, CancellationToken cancellationToken)
    {
        var result = await _productService.GetBySlugAsync(slug, cancellationToken);
        return result is null ? NotFound() : Ok(result);
    }

    [HttpPost]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ProductDto>> Create([FromBody] ProductUpsertRequest request, CancellationToken cancellationToken)
    {
        var result = await _productService.CreateAsync(request, cancellationToken);
        return CreatedAtAction(nameof(GetById), new { id = result.Id }, result);
    }

    [HttpPut("{id:int}")]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ProductDto>> Update(int id, [FromBody] ProductUpsertRequest request, CancellationToken cancellationToken)
    {
        var result = await _productService.UpdateAsync(id, request, cancellationToken);
        return result is null ? NotFound() : Ok(result);
    }

    [HttpDelete("{id:int}")]
    [Authorize(Roles = "Admin")]
    public async Task<IActionResult> Delete(int id, CancellationToken cancellationToken)
    {
        return await _productService.DeleteAsync(id, cancellationToken) ? NoContent() : NotFound();
    }

    [HttpPost("{id:int}/images")]
    [Authorize(Roles = "Admin")]
    [Consumes("multipart/form-data")]
    public async Task<ActionResult<ProductImageDto>> UploadImage(
        int id,
        [FromForm] IFormFile image,
        [FromForm] string? altText,
        [FromForm] bool isMainImage = false,
        [FromForm] int sortOrder = 0,
        CancellationToken cancellationToken = default)
    {
        if (image.Length == 0)
        {
            return BadRequest("Image file is required.");
        }

        await using var stream = image.OpenReadStream();
        var url = await _fileStorageService.SaveImageAsync(stream, image.FileName, "product-images", cancellationToken);
        var result = await _productService.AddImageAsync(id, url, altText, isMainImage, sortOrder, cancellationToken);
        return result is null ? NotFound() : Ok(result);
    }

    [HttpDelete("images/{imageId:int}")]
    [Authorize(Roles = "Admin")]
    public async Task<IActionResult> DeleteImage(int imageId, CancellationToken cancellationToken)
    {
        return await _productService.DeleteImageAsync(imageId, cancellationToken) ? NoContent() : NotFound();
    }

    [HttpPost("{id:int}/qr-code")]
    [Authorize(Roles = "Admin")]
    public async Task<ActionResult<ProductQrCodeDto>> CreateQrCode(
        int id,
        [FromBody] QrCodeCreateRequest request,
        CancellationToken cancellationToken)
    {
        var publicBaseUrl = _configuration["PublicBaseUrl"] ?? "https://localhost:5001";
        var result = await _productService.CreateQrCodeAsync(id, request, publicBaseUrl, cancellationToken);
        return result is null ? NotFound() : Ok(result);
    }
}
