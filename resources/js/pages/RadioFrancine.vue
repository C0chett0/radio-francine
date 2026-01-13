<template>
    <div>
      <Head title="Radio Francine" />

      <div class="min-h-screen bg-slate-950 text-slate-50 flex items-center justify-center">
        <div class="w-full max-w-xl px-6">
          <div
            class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-2xl p-6 flex flex-col items-center gap-6"
          >
            <!-- Installation PWA (mobile) -->
            <div
              v-if="shouldShowPwaInstallBanner"
              class="w-full rounded-xl border border-slate-800 bg-slate-950/50 p-3 sm:hidden"
              data-testid="pwa-install-banner"
            >
              <div class="flex items-start gap-3">
                <div class="shrink-0">
                  <img
                    src="/pwa-icon-192.png"
                    alt="Icône Radio Francine"
                    class="h-9 w-9 rounded-lg"
                    loading="lazy"
                    decoding="async"
                  />
                </div>

                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium leading-5">
                    Installer Radio Francine
                  </p>
                  <p class="mt-0.5 text-xs text-slate-400 leading-4">
                    Accès rapide, plein écran, et disponible hors navigateur.
                  </p>

                  <div class="mt-2 flex items-center gap-2">
                    <button
                      v-if="canPromptPwaInstall"
                      type="button"
                      class="inline-flex items-center justify-center rounded-full bg-emerald-400 px-3 py-1.5 text-xs font-semibold text-slate-950 hover:bg-emerald-300 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300"
                      @click="installPwa"
                      data-testid="pwa-install-action"
                    >
                      Installer
                    </button>

                    <button
                      v-else-if="isIos"
                      type="button"
                      class="inline-flex items-center justify-center rounded-full border border-slate-800 bg-slate-900/60 px-3 py-1.5 text-xs font-semibold text-slate-100 hover:bg-slate-900 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300"
                      @click="dismissPwaInstallBanner"
                      data-testid="pwa-install-ios-ok"
                    >
                      OK
                    </button>

                    <button
                      type="button"
                      class="inline-flex items-center justify-center rounded-full border border-slate-800 bg-slate-900/60 px-3 py-1.5 text-xs font-semibold text-slate-100 hover:bg-slate-900 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300"
                      @click="dismissPwaInstallBanner"
                      data-testid="pwa-install-dismiss"
                    >
                      Plus tard
                    </button>
                  </div>

                  <p
                    v-if="isIos && !canPromptPwaInstall"
                    class="mt-2 text-[11px] text-slate-400 leading-4"
                  >
                    Sur iPhone/iPad: utilisez “Partager” puis “Sur l’écran d’accueil”.
                  </p>
                </div>

                <button
                  type="button"
                  class="shrink-0 rounded-lg p-1 text-slate-400 hover:text-slate-200 hover:bg-slate-900/60 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300"
                  aria-label="Fermer"
                  @click="dismissPwaInstallBanner"
                  data-testid="pwa-install-close"
                >
                  <svg viewBox="0 0 24 24" class="h-4 w-4" fill="currentColor" aria-hidden="true">
                    <path d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12l-4.9 4.89a1 1 0 1 0 1.42 1.42L12 13.41l4.89 4.9a1 1 0 0 0 1.42-1.42L13.41 12l4.9-4.89a1 1 0 0 0-.01-1.4Z" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Titre radio -->
            <div class="text-center">
              <p class="text-xs uppercase tracking-[0.3em] text-slate-400 mb-1">
                En ce moment sur
              </p>
              <h1 class="text-2xl font-semibold tracking-tight">
                {{ stationName || 'Radio Francine' }}
              </h1>
            </div>

            <!-- Artwork + infos chanson -->
            <div class="flex flex-col items-center gap-4 w-full">
              <div class="relative">
                <div
                  class="w-40 h-40 rounded-2xl overflow-hidden bg-slate-800 shadow-lg flex items-center justify-center"
                >
                  <img
                    v-if="current.artwork_url"
                    :src="current.artwork_url"
                    alt="Pochette en cours"
                    class="w-full h-full object-cover"
                  />
                  <div
                    v-else
                    class="w-full h-full flex items-center justify-center text-3xl font-bold text-slate-500"
                  >
                    RF
                  </div>
                </div>
              </div>

              <div class="text-center">
                <p class="text-sm text-slate-400 truncate">
                  {{ current.artist || 'Artiste inconnu' }}
                </p>
                <p class="text-lg font-medium truncate">
                  {{ current.title || 'Titre inconnu' }}
                </p>
              </div>
            </div>

            <!-- Barre de progression -->
            <div class="w-full">
              <div class="flex justify-between text-xs text-slate-400 mb-1">
                <span>{{ formatSeconds(progressSeconds) }}</span>
                <span v-if="current.duration">
                  {{ formatSeconds(current.duration) }}
                </span>
              </div>
              <div class="h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div
                  class="h-full bg-emerald-400 transition-[width] duration-300 ease-out"
                  :style="{ width: progressPercent + '%' }"
                ></div>
              </div>
            </div>

          <!-- Contrôles -->
          <div class="flex items-center gap-4 w-full mt-3">
            <button
              type="button"
              @click="togglePlayback"
              class="relative w-16 h-16 rounded-full bg-gradient-to-tr from-emerald-500 via-emerald-300 to-lime-200 text-slate-950 shadow-[0_14px_45px_-18px_rgba(16,185,129,0.7)] hover:shadow-[0_18px_55px_-18px_rgba(74,222,128,0.85)] transition duration-200 hover:scale-105 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300 disabled:opacity-60 disabled:cursor-not-allowed"
              :disabled="isLoading"
              aria-label="Lecture"
            >
              <span class="absolute inset-[2px] rounded-full bg-slate-950/75" />
              <span class="relative flex items-center justify-center w-full h-full">
                <svg
                  v-if="!isPlaying"
                  viewBox="0 0 24 24"
                  class="w-7 h-7"
                  fill="currentColor"
                >
                  <path d="M7 5.5v13a1 1 0 0 0 1.52.85l9-6.5a1 1 0 0 0 0-1.7l-9-6.5A1 1 0 0 0 7 5.5Z" />
                </svg>
                <svg
                  v-else
                  viewBox="0 0 24 24"
                  class="w-7 h-7"
                  fill="currentColor"
                >
                  <path d="M7 6a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V6Zm8 0a1 1 0 0 1 1-1h1.5a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H16a1 1 0 0 1-1-1V6Z" />
                </svg>
              </span>
            </button>

            <div class="flex-1">
              <div class="flex items-center gap-3 w-full rounded-full bg-slate-900/70 border border-slate-800/80 px-4 py-2.5 shadow-inner shadow-black/30">
                <button
                  type="button"
                  class="shrink-0 -ml-1 rounded-full p-1 text-slate-400 hover:text-slate-200 hover:bg-slate-900/60 transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-300"
                  :aria-label="isMuted ? 'Rétablir le son' : 'Couper le son'"
                  @click="toggleMute"
                  data-testid="mute-toggle"
                >
                  <svg
                    v-if="isMuted"
                    viewBox="0 0 24 24"
                    class="w-5 h-5"
                    fill="currentColor"
                    aria-hidden="true"
                  >
                    <path d="M4 10v4c0 .55.45 1 1 1h2.38L11 18.5a1 1 0 0 0 1.58-.82V6.32A1 1 0 0 0 11 5.5L7.38 9H5c-.55 0-1 .45-1 1Z" />
                    <path d="M16.2 8.8a1 1 0 0 1 1.4 0L19 10.2l1.4-1.4a1 1 0 1 1 1.4 1.4L20.4 11.6l1.4 1.4a1 1 0 0 1-1.4 1.4L19 13l-1.4 1.4a1 1 0 1 1-1.4-1.4l1.4-1.4-1.4-1.4a1 1 0 0 1 0-1.4Z" />
                  </svg>

                  <svg
                    v-else
                    viewBox="0 0 24 24"
                    class="w-5 h-5"
                    fill="currentColor"
                    aria-hidden="true"
                  >
                    <path d="M4 10v4c0 .55.45 1 1 1h2.38L11 18.5a1 1 0 0 0 1.58-.82V6.32A1 1 0 0 0 11 5.5L7.38 9H5c-.55 0-1 .45-1 1Z" />
                    <path
                      fill="none"
                      stroke="currentColor"
                      stroke-linecap="round"
                      d="M15 10.5a3 3 0 0 1 0 3"
                    />
                  </svg>
                </button>

                <input
                  type="range"
                  min="0"
                  max="1"
                  step="0.01"
                  v-model.number="volume"
                  @input="onVolumeChange"
                  class="player-range w-full cursor-pointer"
                  aria-label="Volume"
                />

                <svg
                  viewBox="0 0 24 24"
                  class="w-5 h-5 text-emerald-300 shrink-0"
                  aria-hidden="true"
                >
                  <path
                    fill="currentColor"
                    d="M4 10v4c0 .55.45 1 1 1h2.38L11 18.5a1 1 0 0 0 1.58-.82V6.32A1 1 0 0 0 11 5.5L7.38 9H5c-.55 0-1 .45-1 1Z"
                  />
                  <path
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    d="M15.5 8a4.5 4.5 0 0 1 0 8M18.5 6.5a6.5 6.5 0 0 1 0 11"
                  />
                </svg>
              </div>
            </div>
          </div>

            <!-- Historique -->
            <div
              v-if="history.length"
              class="w-full mt-4 border-t border-slate-800 pt-4"
            >
              <p class="text-xs uppercase tracking-[0.2em] text-slate-500 mb-3">
                Récemment
              </p>
              <ul
                class="space-y-1 text-sm text-slate-300 max-h-40 overflow-y-auto pr-3 history-scroll"
              >
                <li
                  v-for="track in history"
                  :key="track.played_at + '-' + (track.artist || '') + '-' + (track.title || '')"
                  class="flex items-center justify-between gap-2"
                >
                  <div class="truncate">
                    <span class="font-medium">
                      {{ track.artist || 'Artiste inconnu' }}
                    </span>
                    <span class="text-slate-400">
                      — {{ track.title || 'Titre inconnu' }}
                    </span>
                  </div>
                  <span class="text-xs text-slate-500 whitespace-nowrap">
                    {{ formatHistoryTime(track.played_at) }}
                  </span>
                </li>
              </ul>
            </div>

            <!-- Erreur éventuelle -->
            <p v-if="error" class="text-xs text-red-400 mt-2">
              {{ error }}
            </p>

            <!-- Audio réel -->
            <audio
              ref="audio"
              preload="none"
              playsinline
            ></audio>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script setup lang="ts">
  import { Head } from '@inertiajs/vue3';
  import {
    computed,
    onBeforeUnmount,
    onMounted,
    reactive,
    ref,
    watch,
  } from 'vue';
  import Hls from 'hls.js';

  interface CurrentTrack {
    artist: string | null;
    title: string | null;
    artwork_url: string | null;
    duration: number | null;
    played_at: number | null;
  }

  interface HistoryEntry {
    artist: string | null;
    title: string | null;
    artwork_url: string | null;
    played_at: number | null;
  }

  interface NowPlayingApiResponse {
    station_name?: string;
    stream?: {
      mp3: string;
      hls: string;
    };
    now_playing?: {
      artist: string | null;
      title: string | null;
      artwork_url: string | null;
      duration: number | null;
      elapsed?: number | null;
      played_at?: number | null;
    };
    history?: HistoryEntry[];
  }

  const stationName = ref<string>('Radio Francine');
  const isPlaying = ref<boolean>(false);
  const isLoading = ref<boolean>(false);
  const error = ref<string | null>(null);

  const volume = ref<number>(0.8);
  const audio = ref<HTMLAudioElement | null>(null);
  const isMuted = ref<boolean>(false);
  const volumeBeforeMute = ref<number>(0.8);

  type BeforeInstallPromptEvent = Event & {
    prompt: () => Promise<void>;
    userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
  };

  const pwaDeferredPrompt = ref<BeforeInstallPromptEvent | null>(null);
  const isMobileDevice = ref<boolean>(false);
  const isStandalone = ref<boolean>(false);
  const isIos = ref<boolean>(false);
  const hasDismissedPwaInstall = ref<boolean>(false);

  const current = reactive<CurrentTrack>({
    artist: null,
    title: null,
    artwork_url: null,
    duration: null,
    played_at: null,
  });

  const history = ref<HistoryEntry[]>([]);

  const progressSeconds = ref<number>(0);
  let progressTimer: number | undefined;
  let pollingTimer: number | undefined;

  const hlsStreamUrl = 'https://cast.cochet.cloud/hls/francine/live.m3u8';
  const mp3StreamUrl = 'https://cast.cochet.cloud/listen/francine/radio.mp3';
  const defaultArtwork = '/pwa-icon-512.png';

  const streamTech = ref<'hls' | 'mp3'>('mp3');
  let hlsInstance: Hls | null = null;

  let ws: WebSocket | null = null;
  let wsReconnectTimer: number | undefined;
  let removeAudioListeners: (() => void) | null = null;
  let removePwaInstallPromptListener: (() => void) | null = null;

  const progressPercent = computed<number>(() => {
    if (!current.duration || current.duration <= 0) return 0;
    const ratio = progressSeconds.value / current.duration;
    return Math.max(0, Math.min(100, ratio * 100));
  });

  function updateMediaSessionPlaybackState(playing: boolean): void {
    if (!('mediaSession' in navigator)) {
      return;
    }

    navigator.mediaSession.playbackState = playing ? 'playing' : 'paused';
  }

  function updateMediaSessionMetadata(): void {
    if (!('mediaSession' in navigator)) {
      return;
    }

    const artwork = current.artwork_url || defaultArtwork;
    const title = current.title || 'Radio Francine';
    const artist = current.artist || stationName.value || 'Radio Francine';

    navigator.mediaSession.metadata = new MediaMetadata({
      title,
      artist,
      album: stationName.value || 'Radio Francine',
      artwork: [
        { src: artwork, sizes: '192x192', type: 'image/png' },
        { src: artwork, sizes: '512x512', type: 'image/png' },
      ],
    });
  }

  function setupMediaSessionActions(): void {
    if (!('mediaSession' in navigator)) {
      return;
    }

    navigator.mediaSession.setActionHandler('play', async () => {
      if (!isPlaying.value) {
        await togglePlayback();
      }
    });

    navigator.mediaSession.setActionHandler('pause', () => {
      if (isPlaying.value) {
        void togglePlayback();
      }
    });

    navigator.mediaSession.setActionHandler('stop', () => {
      if (isPlaying.value) {
        void togglePlayback();
      }
    });
  }

  function attachAudioEvents(): void {
    const el = audio.value;
    if (!el) return;

    const onPlay = () => {
      isPlaying.value = true;
      updateMediaSessionPlaybackState(true);
    };

    const onPause = () => {
      isPlaying.value = false;
      updateMediaSessionPlaybackState(false);
    };

    const onEnded = () => {
      isPlaying.value = false;
      updateMediaSessionPlaybackState(false);
    };

    el.addEventListener('play', onPlay);
    el.addEventListener('pause', onPause);
    el.addEventListener('ended', onEnded);

    removeAudioListeners = () => {
      el.removeEventListener('play', onPlay);
      el.removeEventListener('pause', onPause);
      el.removeEventListener('ended', onEnded);
      removeAudioListeners = null;
    };
  }

  function setupStream(): void {
    const el = audio.value;
    if (!el) return;

    if (hlsInstance) {
      hlsInstance.destroy();
      hlsInstance = null;
    }

    if (Hls.isSupported()) {
      hlsInstance = new Hls({
        enableWorker: true,
        lowLatencyMode: true,
      });
      hlsInstance.loadSource(hlsStreamUrl);
      hlsInstance.attachMedia(el);
      streamTech.value = 'hls';
      el.volume = volume.value;
      return;
    }

    const canPlayHls =
      el.canPlayType('application/vnd.apple.mpegurl') !== '' ||
      el.canPlayType('audio/mpegurl') !== '';

    if (canPlayHls) {
      el.src = hlsStreamUrl;
      streamTech.value = 'hls';
      el.load();
      el.volume = volume.value;
      return;
    }

    el.src = mp3StreamUrl;
    streamTech.value = 'mp3';
    el.load();
    el.volume = volume.value;
  }

  async function fetchNowPlaying(): Promise<void> {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await fetch('/rest/radio/francine/now-playing', {
        headers: {
          Accept: 'application/json',
        },
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data: NowPlayingApiResponse = await response.json();

      stationName.value = data.station_name ?? 'Radio Francine';

      if (data.now_playing) {
        current.artist = data.now_playing.artist ?? null;
        current.title = data.now_playing.title ?? null;
        current.artwork_url = data.now_playing.artwork_url ?? null;
        current.duration = data.now_playing.duration ?? null;
        current.played_at = data.now_playing.played_at ?? null;

        if (data.now_playing.played_at && data.now_playing.duration) {
          const now = Math.floor(Date.now() / 1000);
          const elapsed = now - data.now_playing.played_at;
          progressSeconds.value = Math.max(
            0,
            Math.min(data.now_playing.duration, elapsed),
          );
        } else if (
          typeof data.now_playing.elapsed === 'number' &&
          data.now_playing.elapsed >= 0
        ) {
          progressSeconds.value = data.now_playing.elapsed;
        } else {
          progressSeconds.value = 0;
        }
      } else {
        current.artist = null;
        current.title = null;
        current.artwork_url = null;
        current.duration = null;
        current.played_at = null;
        progressSeconds.value = 0;
      }

      history.value = Array.isArray(data.history) ? data.history : [];
      updateMediaSessionMetadata();
    } catch (e) {
      console.error(e);
      error.value = 'Impossible de charger les informations de la radio.';
    } finally {
      isLoading.value = false;
      if (pollingTimer !== undefined) {
        window.clearTimeout(pollingTimer);
      }
      // Polling de secours toutes les 60s
      pollingTimer = window.setTimeout(fetchNowPlaying, 60000);
    }
  }

  function startProgressTimer(): void {
    progressTimer = window.setInterval(() => {
      if (!current.duration || current.duration <= 0) {
        return;
      }
      if (progressSeconds.value < current.duration) {
        progressSeconds.value += 1;
      }
    }, 1000);
  }

  async function togglePlayback(): Promise<void> {
    const el = audio.value;
    if (!el) return;

    if (!isPlaying.value) {
      try {
        await el.play();
        isPlaying.value = true;
      } catch (e) {
        console.error(e);
        error.value = 'La lecture a été bloquée par le navigateur.';
      }
    } else {
      el.pause();
      isPlaying.value = false;
    }
  }

  function onVolumeChange(): void {
    const el = audio.value;
    if (!el) {
      return;
    }

    el.volume = volume.value;

    if (volume.value <= 0) {
      isMuted.value = true;
      return;
    }

    volumeBeforeMute.value = volume.value;
    isMuted.value = false;
  }

  function toggleMute(): void {
    const el = audio.value;
    if (!el) {
      return;
    }

    if (!isMuted.value) {
      volumeBeforeMute.value = volume.value > 0 ? volume.value : el.volume || 0.8;
      volume.value = 0;
      el.volume = 0;
      isMuted.value = true;
      return;
    }

    const restoredVolume = volumeBeforeMute.value > 0 ? volumeBeforeMute.value : 0.8;
    volume.value = restoredVolume;
    el.volume = restoredVolume;
    isMuted.value = false;
  }

  const canPromptPwaInstall = computed<boolean>(() => pwaDeferredPrompt.value !== null);

  const shouldShowPwaInstallBanner = computed<boolean>(() => {
    return (
      isMobileDevice.value &&
      !isStandalone.value &&
      !hasDismissedPwaInstall.value &&
      (canPromptPwaInstall.value || isIos.value)
    );
  });

  function dismissPwaInstallBanner(): void {
    hasDismissedPwaInstall.value = true;
    window.localStorage.setItem('rf:pwa-install-dismissed', '1');
  }

  async function installPwa(): Promise<void> {
    const evt = pwaDeferredPrompt.value;
    if (!evt) {
      dismissPwaInstallBanner();
      return;
    }

    try {
      await evt.prompt();
      await evt.userChoice;
    } finally {
      pwaDeferredPrompt.value = null;
      dismissPwaInstallBanner();
    }
  }

  function formatSeconds(sec?: number | null): string {
    if (!sec || sec < 0) {
      return '0:00';
    }
    const total = Math.floor(sec);
    const minutes = Math.floor(total / 60);
    const seconds = total % 60;
    return `${minutes}:${seconds.toString().padStart(2, '0')}`;
  }

  function formatHistoryTime(playedAt?: number | null): string {
    if (!playedAt) return '';
    const date = new Date(playedAt * 1000);
    return date.toLocaleTimeString(undefined, {
      hour: '2-digit',
      minute: '2-digit',
    });
  }

  function applyNowPlayingFromNp(np: any): void {
    if (!np) return;

    const now = np.now_playing ?? null;
    const song = now?.song ?? {};

    current.artist = song.artist ?? null;
    current.title = song.title ?? null;
    current.artwork_url = song.art ?? null;
    current.duration =
      typeof now?.duration === 'number' ? now.duration : null;
    current.played_at =
      typeof now?.played_at === 'number' ? now.played_at : null;

    if (now?.played_at && now?.duration) {
      const nowClient = Math.floor(Date.now() / 1000);
      const elapsed = nowClient - now.played_at;
      progressSeconds.value = Math.max(0, Math.min(now.duration, elapsed));
    } else if (typeof now?.elapsed === 'number') {
      progressSeconds.value = now.elapsed;
    } else {
      progressSeconds.value = 0;
    }

    const hist = Array.isArray(np.song_history) ? np.song_history : [];
    history.value = hist.map((entry: any) => ({
      artist: entry.song?.artist ?? null,
      title: entry.song?.title ?? null,
      artwork_url: entry.song?.art ?? null,
      played_at:
        typeof entry.played_at === 'number' ? entry.played_at : null,
    }));
    updateMediaSessionMetadata();
  }

  function handleCentrifugoPayload(payload: any): void {
    const jsonData = payload?.data ?? payload;

    if (jsonData.np) {
      applyNowPlayingFromNp(jsonData.np);
    }
  }

  function setupWebSocket(): void {
    const url = 'wss://cast.cochet.cloud/api/live/nowplaying/websocket';

    try {
      ws = new WebSocket(url);
    } catch (e) {
      console.error('WebSocket init error', e);
      return;
    }

    ws.onopen = () => {
      const subs = {
        subs: {
          'station:francine': { recover: true },
        },
      };
      ws?.send(JSON.stringify(subs));
    };

    ws.onmessage = (event: MessageEvent) => {
      try {
        const jsonData = JSON.parse(event.data);

        if ('connect' in jsonData) {
          const connectData = jsonData.connect;

          if ('data' in connectData && Array.isArray(connectData.data)) {
            connectData.data.forEach((initialRow: any) =>
              handleCentrifugoPayload(initialRow),
            );
          } else {
            if (connectData.subs) {
              Object.values(connectData.subs).forEach((sub: any) => {
                if (Array.isArray(sub.publications)) {
                  sub.publications.forEach((initialRow: any) =>
                    handleCentrifugoPayload(initialRow),
                  );
                }
              });
            }
          }
        } else if ('pub' in jsonData) {
          handleCentrifugoPayload(jsonData.pub);
        }
      } catch (err) {
        console.error('WebSocket message parse error', err);
      }
    };

    ws.onerror = (event: Event) => {
      console.error('WebSocket error', event);
    };

    ws.onclose = () => {
      ws = null;
      if (wsReconnectTimer !== undefined) {
        window.clearTimeout(wsReconnectTimer);
      }
      wsReconnectTimer = window.setTimeout(() => {
        setupWebSocket();
      }, 5000);
    };
  }

  watch(
    () => [
      current.title,
      current.artist,
      current.artwork_url,
      stationName.value,
    ],
    () => updateMediaSessionMetadata(),
  );

  watch(isPlaying, (playing) => {
    updateMediaSessionPlaybackState(playing);
  });

  onMounted(() => {
    isMobileDevice.value =
      window.matchMedia('(pointer: coarse)').matches ||
      window.matchMedia('(max-width: 640px)').matches;

    isStandalone.value =
      window.matchMedia('(display-mode: standalone)').matches ||
      // iOS Safari
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      Boolean((navigator as any).standalone);

    const ua = navigator.userAgent ?? '';
    isIos.value =
      /iPad|iPhone|iPod/.test(ua) &&
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      !(window as any).MSStream;

    hasDismissedPwaInstall.value =
      window.localStorage.getItem('rf:pwa-install-dismissed') === '1';

    const onBeforeInstallPrompt = (e: Event) => {
      e.preventDefault();
      pwaDeferredPrompt.value = e as BeforeInstallPromptEvent;
    };

    window.addEventListener('beforeinstallprompt', onBeforeInstallPrompt);
    removePwaInstallPromptListener = () => {
      window.removeEventListener('beforeinstallprompt', onBeforeInstallPrompt);
      removePwaInstallPromptListener = null;
    };

    if (audio.value) {
      audio.value.volume = volume.value;
    }

    volumeBeforeMute.value = volume.value;
    isMuted.value = volume.value <= 0;

    setupStream();
    fetchNowPlaying();
    startProgressTimer();
    setupWebSocket();
    attachAudioEvents();
    setupMediaSessionActions();
    updateMediaSessionMetadata();
    updateMediaSessionPlaybackState(isPlaying.value);
  });

  onBeforeUnmount(() => {
    if (progressTimer !== undefined) {
      window.clearInterval(progressTimer);
    }
    if (pollingTimer !== undefined) {
      window.clearTimeout(pollingTimer);
    }
    if (ws) {
      ws.close();
    }
    if (hlsInstance) {
      hlsInstance.destroy();
    }
    if (removeAudioListeners) {
      removeAudioListeners();
    }
    if (removePwaInstallPromptListener) {
      removePwaInstallPromptListener();
    }
  });
  </script>

  <style scoped>
  .min-h-screen {
    min-height: 100vh;
  }

  .bg-slate-950 {
    background: radial-gradient(circle at top, #1f2937 0, #020617 60%, #000 100%);
  }

  .player-range {
    --thumb-size: 16px;
    appearance: none;
    height: 10px;
    border-radius: 9999px;
    background: #111827;
    outline: none;
  }

  .player-range::-webkit-slider-thumb {
    appearance: none;
    width: var(--thumb-size);
    height: var(--thumb-size);
    border-radius: 9999px;
    background: #34d399;
    border: 2px solid #0f172a;
    box-shadow: 0 10px 28px -14px rgba(52, 211, 153, 0.9);
  }

  .player-range::-moz-range-thumb {
    width: var(--thumb-size);
    height: var(--thumb-size);
    border-radius: 9999px;
    background: #34d399;
    border: 2px solid #0f172a;
    box-shadow: 0 10px 28px -14px rgba(52, 211, 153, 0.9);
  }

  .player-range::-moz-range-track {
    height: 10px;
    border-radius: 9999px;
    background: #111827;
  }

  .history-scroll {
    scrollbar-color: #34d399 #0b1224;
    scrollbar-width: thin;
  }

  .history-scroll::-webkit-scrollbar {
    width: 8px;
  }

  .history-scroll::-webkit-scrollbar-track {
    background: #0b1224;
    border-radius: 9999px;
  }

  .history-scroll::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #34d399, #22c55e);
    border-radius: 9999px;
    border: 2px solid #0b1224;
    box-shadow: 0 8px 18px -12px rgba(52, 211, 153, 0.8);
  }
  </style>
