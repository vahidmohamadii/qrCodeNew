using Microsoft.EntityFrameworkCore;
using QrCode.Application.Abstractions;
using QrCode.Application.Auth;
using QrCode.Domain.Entities;
using QrCode.Infrastructure.Data;

namespace QrCode.Infrastructure.Services;

internal sealed class AuthService : IAuthService
{
    private readonly AppDbContext _dbContext;
    private readonly IJwtTokenService _jwtTokenService;

    public AuthService(AppDbContext dbContext, IJwtTokenService jwtTokenService)
    {
        _dbContext = dbContext;
        _jwtTokenService = jwtTokenService;
    }

    public async Task<AuthResponse?> LoginAsync(LoginRequest request, CancellationToken cancellationToken = default)
    {
        var user = await _dbContext.Users.FirstOrDefaultAsync(x => x.Email == request.Email && x.IsActive, cancellationToken);
        if (user is null || !SecurityHelpers.VerifyPassword(request.Password, user.PasswordHash))
        {
            return null;
        }

        return new AuthResponse
        {
            AccessToken = _jwtTokenService.CreateToken(user.Email, user.FullName, user.Role),
            FullName = user.FullName,
            Email = user.Email,
            Role = user.Role
        };
    }

    public async Task<CurrentUserDto?> GetCurrentUserAsync(string email, CancellationToken cancellationToken = default)
    {
        var user = await _dbContext.Users.FirstOrDefaultAsync(x => x.Email == email && x.IsActive, cancellationToken);
        return user is null
            ? null
            : new CurrentUserDto
            {
                FullName = user.FullName,
                Email = user.Email,
                Role = user.Role
            };
    }
}
