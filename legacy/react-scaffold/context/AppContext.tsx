import { createContext, useCallback, useContext, useEffect, useMemo, useState, type ReactNode } from 'react';
import type { AppData, UserState } from '../types/data';
import { loadAppData, buildVocabMap } from '../lib/data';
import {
  getState, saveState, updateStudyStreak, recalcHskProgress, setSetting, setPremium,
} from '../lib/storage';

type Ctx = {
  data: AppData | null;
  loading: boolean;
  error: string | null;
  state: UserState;
  refreshState: () => void;
  setState: (s: UserState) => void;
  vocabMap: ReturnType<typeof buildVocabMap>;
  isPremium: boolean;
  toggleDark: () => void;
  demoPremium: () => void;
};

const AppContext = createContext<Ctx | null>(null);

export function AppProvider({ children }: { children: ReactNode }) {
  const [data, setData] = useState<AppData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [state, setStateLocal] = useState<UserState>(() => getState());

  const refreshState = useCallback(() => setStateLocal(getState()), []);
  const setState = useCallback((s: UserState) => {
    saveState(s);
    setStateLocal(s);
  }, []);

  useEffect(() => {
    loadAppData()
      .then(d => {
        setData(d);
        updateStudyStreak(getState());
        const s = recalcHskProgress(d.lessons.levels);
        setStateLocal(s);
      })
      .catch(e => setError(e.message))
      .finally(() => setLoading(false));
  }, []);

  useEffect(() => {
    document.documentElement.setAttribute(
      'data-theme',
      state.settings.darkMode ? 'dark' : 'light'
    );
  }, [state.settings.darkMode]);

  const vocabMap = useMemo(
    () => buildVocabMap(data?.vocabulary.words || []),
    [data]
  );

  const toggleDark = useCallback(() => {
    setState(setSetting('darkMode', !state.settings.darkMode));
    refreshState();
  }, [state.settings.darkMode, refreshState, setState]);

  const demoPremium = useCallback(() => {
    setState(setPremium(true));
    refreshState();
  }, [refreshState, setState]);

  const value: Ctx = {
    data,
    loading,
    error,
    state,
    refreshState,
    setState,
    vocabMap,
    isPremium: state.isPremium,
    toggleDark,
    demoPremium,
  };

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp() {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error('useApp must be inside AppProvider');
  return ctx;
}
