import { Injectable } from '@angular/core';
import { HttpHeaders } from '@angular/common/http';
import { AuthResponse } from './models';

const STORAGE_KEY = 'qrcode_catalog_auth';

@Injectable({ providedIn: 'root' })
export class AuthService {
  setSession(auth: AuthResponse): void {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(auth));
  }

  logout(): void {
    localStorage.removeItem(STORAGE_KEY);
  }

  currentUser(): AuthResponse | null {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw) as AuthResponse;
    } catch {
      this.logout();
      return null;
    }
  }

  token(): string | null {
    return this.currentUser()?.accessToken ?? null;
  }

  isAuthenticated(): boolean {
    return this.token() !== null;
  }

  isAdmin(): boolean {
    return this.currentUser()?.role.toLowerCase() === 'admin';
  }

  authHeaders(): HttpHeaders {
    const token = this.token();
    return token ? new HttpHeaders({ Authorization: `Bearer ${token}` }) : new HttpHeaders();
  }
}
