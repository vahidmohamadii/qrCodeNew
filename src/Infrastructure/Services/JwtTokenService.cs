using System.IdentityModel.Tokens.Jwt;
using System.Security.Claims;
using System.Text;
using Microsoft.Extensions.Configuration;
using Microsoft.IdentityModel.Tokens;
using QrCode.Application.Abstractions;

namespace QrCode.Infrastructure.Services;

internal sealed class JwtTokenService : IJwtTokenService
{
    private readonly IConfiguration _configuration;

    public JwtTokenService(IConfiguration configuration)
    {
        _configuration = configuration;
    }

    public string CreateToken(string email, string fullName, string role)
    {
        var key = _configuration["Jwt:Key"] ?? "change-this-secret-key-before-production";
        var issuer = _configuration["Jwt:Issuer"] ?? "QrCode";
        var audience = _configuration["Jwt:Audience"] ?? "QrCode";
        var minutes = int.TryParse(_configuration["Jwt:Minutes"], out var tokenMinutes) ? tokenMinutes : 120;

        var claims = new[]
        {
            new Claim(ClaimTypes.Email, email),
            new Claim(ClaimTypes.Name, fullName),
            new Claim(ClaimTypes.Role, role)
        };

        var credentials = new SigningCredentials(
            new SymmetricSecurityKey(Encoding.UTF8.GetBytes(key)),
            SecurityAlgorithms.HmacSha256);

        var token = new JwtSecurityToken(
            issuer,
            audience,
            claims,
            expires: DateTime.UtcNow.AddMinutes(minutes),
            signingCredentials: credentials);

        return new JwtSecurityTokenHandler().WriteToken(token);
    }
}
