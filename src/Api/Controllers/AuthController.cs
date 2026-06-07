using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using QrCode.Application.Abstractions;
using QrCode.Application.Auth;

namespace QrCode.Api.Controllers;

[ApiController]
[Route("api/auth")]
public sealed class AuthController : ControllerBase
{
    private readonly IAuthService _authService;

    public AuthController(IAuthService authService)
    {
        _authService = authService;
    }

    [HttpPost("login")]
    [AllowAnonymous]
    public async Task<ActionResult<AuthResponse>> Login([FromBody] LoginRequest request, CancellationToken cancellationToken)
    {
        var result = await _authService.LoginAsync(request, cancellationToken);
        return result is null ? Unauthorized() : Ok(result);
    }

    [HttpGet("me")]
    [Authorize]
    public async Task<ActionResult<CurrentUserDto>> Me([FromQuery] string email, CancellationToken cancellationToken)
    {
        var result = await _authService.GetCurrentUserAsync(email, cancellationToken);
        return result is null ? NotFound() : Ok(result);
    }
}
