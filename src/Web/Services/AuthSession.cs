using QrCode.Application.Auth;

namespace QrCode.Web.Services;

public sealed class AuthSession
{
    public string? AccessToken { get; private set; }

    public AuthResponse? CurrentUser { get; private set; }

    public bool IsAuthenticated => !string.IsNullOrWhiteSpace(AccessToken);

    public void Set(AuthResponse response)
    {
        AccessToken = response.AccessToken;
        CurrentUser = response;
    }

    public void Clear()
    {
        AccessToken = null;
        CurrentUser = null;
    }
}
