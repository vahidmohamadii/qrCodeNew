using System.Net.Http.Headers;
using System.Net.Http.Json;
using Microsoft.AspNetCore.Components.Forms;
using QrCode.Application.Auth;
using QrCode.Application.Catalog;
using QrCode.Application.Company;

namespace QrCode.Web.Services;

public sealed class ApiClient
{
    private readonly HttpClient _httpClient;
    private readonly AuthSession _authSession;

    public ApiClient(HttpClient httpClient, AuthSession authSession)
    {
        _httpClient = httpClient;
        _authSession = authSession;
    }

    public async Task<IReadOnlyList<ProductDto>> GetProductsAsync(string? search = null, int? categoryId = null)
    {
        try
        {
            var url = QueryString.Combine("/api/products", search, categoryId);
            return await _httpClient.GetFromJsonAsync<IReadOnlyList<ProductDto>>(url) ?? Array.Empty<ProductDto>();
        }
        catch (HttpRequestException)
        {
            return Array.Empty<ProductDto>();
        }
    }

    public async Task<ProductDto?> GetProductByIdAsync(int id)
    {
        try
        {
            return await _httpClient.GetFromJsonAsync<ProductDto>($"/api/products/{id}");
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task<PublicProductDto?> GetProductBySlugAsync(string slug)
    {
        try
        {
            return await _httpClient.GetFromJsonAsync<PublicProductDto>($"/api/products/by-slug/{slug}");
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task<IReadOnlyList<CategoryDto>> GetCategoriesAsync(string? search = null)
    {
        try
        {
            var url = QueryString.Combine("/api/categories", search);
            return await _httpClient.GetFromJsonAsync<IReadOnlyList<CategoryDto>>(url) ?? Array.Empty<CategoryDto>();
        }
        catch (HttpRequestException)
        {
            return Array.Empty<CategoryDto>();
        }
    }

    public async Task<CompanyInfoDto?> GetCompanyInfoAsync()
    {
        try
        {
            return await _httpClient.GetFromJsonAsync<CompanyInfoDto>("/api/company-info");
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task<AuthResponse?> LoginAsync(LoginRequest request)
    {
        try
        {
            var response = await _httpClient.PostAsJsonAsync("/api/auth/login", request);
            if (!response.IsSuccessStatusCode)
            {
                return null;
            }

            var auth = await response.Content.ReadFromJsonAsync<AuthResponse>();
            if (auth is not null)
            {
                _authSession.Set(auth);
            }

            return auth;
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task<CategoryDto?> CreateCategoryAsync(CategoryUpsertRequest request)
    {
        try
        {
            var response = await SendAuthorizedAsync(HttpMethod.Post, "/api/categories", request);
            return await ReadResponseAsync<CategoryDto>(response);
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task<CategoryDto?> UpdateCategoryAsync(int id, CategoryUpsertRequest request)
    {
        try
        {
            var response = await SendAuthorizedAsync(HttpMethod.Put, $"/api/categories/{id}", request);
            return await ReadResponseAsync<CategoryDto>(response);
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task DeleteCategoryAsync(int id)
    {
        using var response = await SendAuthorizedAsync(HttpMethod.Delete, $"/api/categories/{id}");
        response.EnsureSuccessStatusCode();
    }

    public async Task<ProductDto?> CreateProductAsync(ProductUpsertRequest request)
    {
        try
        {
            var response = await SendAuthorizedAsync(HttpMethod.Post, "/api/products", request);
            return await ReadResponseAsync<ProductDto>(response);
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task<ProductDto?> UpdateProductAsync(int id, ProductUpsertRequest request)
    {
        try
        {
            var response = await SendAuthorizedAsync(HttpMethod.Put, $"/api/products/{id}", request);
            return await ReadResponseAsync<ProductDto>(response);
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task DeleteProductAsync(int id)
    {
        using var response = await SendAuthorizedAsync(HttpMethod.Delete, $"/api/products/{id}");
        response.EnsureSuccessStatusCode();
    }

    public async Task<ProductImageDto?> UploadProductImageAsync(int productId, IBrowserFile file, string? altText, bool isMainImage, int sortOrder)
    {
        try
        {
            await using var stream = file.OpenReadStream(maxAllowedSize: 10 * 1024 * 1024);
            using var form = new MultipartFormDataContent();
            var fileContent = new StreamContent(stream);
            fileContent.Headers.ContentType = new MediaTypeHeaderValue(file.ContentType);
            form.Add(fileContent, "image", file.Name);
            form.Add(new StringContent(altText ?? string.Empty), "altText");
            form.Add(new StringContent(isMainImage.ToString()), "isMainImage");
            form.Add(new StringContent(sortOrder.ToString()), "sortOrder");

            var request = new HttpRequestMessage(HttpMethod.Post, $"/api/products/{productId}/images")
            {
                Content = form
            };
            AddAuthorization(request);

            using var response = await _httpClient.SendAsync(request);
            return await ReadResponseAsync<ProductImageDto>(response);
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    public async Task<ProductQrCodeDto?> CreateQrCodeAsync(int productId, QrCodeCreateRequest request)
    {
        try
        {
            var response = await SendAuthorizedAsync(HttpMethod.Post, $"/api/products/{productId}/qr-code", request);
            return await ReadResponseAsync<ProductQrCodeDto>(response);
        }
        catch (HttpRequestException)
        {
            return null;
        }
    }

    private async Task<HttpResponseMessage> SendAuthorizedAsync<T>(HttpMethod method, string uri, T payload)
    {
        var request = new HttpRequestMessage(method, uri)
        {
            Content = JsonContent.Create(payload)
        };
        AddAuthorization(request);
        return await _httpClient.SendAsync(request);
    }

    private async Task<HttpResponseMessage> SendAuthorizedAsync(HttpMethod method, string uri)
    {
        var request = new HttpRequestMessage(method, uri);
        AddAuthorization(request);
        return await _httpClient.SendAsync(request);
    }

    private void AddAuthorization(HttpRequestMessage request)
    {
        if (!string.IsNullOrWhiteSpace(_authSession.AccessToken))
        {
            request.Headers.Authorization = new AuthenticationHeaderValue("Bearer", _authSession.AccessToken);
        }
    }

    private static async Task<T?> ReadResponseAsync<T>(HttpResponseMessage response)
    {
        if (!response.IsSuccessStatusCode)
        {
            return default;
        }

        return await response.Content.ReadFromJsonAsync<T>();
    }
}

internal static class QueryString
{
    public static string Combine(string basePath, string? search = null, int? categoryId = null)
    {
        var query = new List<string>();
        if (!string.IsNullOrWhiteSpace(search))
        {
            query.Add($"search={Uri.EscapeDataString(search)}");
        }

        if (categoryId.HasValue)
        {
            query.Add($"categoryId={categoryId}");
        }

        return query.Count == 0 ? basePath : $"{basePath}?{string.Join("&", query)}";
    }
}
