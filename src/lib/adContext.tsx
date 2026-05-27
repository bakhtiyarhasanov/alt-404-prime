import { createContext, useContext, useState } from 'react';

export type AdZone = {
  id: string;
  label: string;
  enabled: boolean;
  imageUrl: string;
  linkUrl: string;
  width: number;
  height: number;
};

const defaultAds: AdZone[] = [
  {
    id: 'leaderboard',
    label: 'Leaderboard (üst banner)',
    enabled: true,
    imageUrl: 'https://images.pexels.com/photos/1779487/pexels-photo-1779487.jpeg?auto=compress&cs=tinysrgb&w=900&h=90&fit=crop',
    linkUrl: '#',
    width: 900,
    height: 90,
  },
  {
    id: 'sidebar-left',
    label: 'Sol Panel',
    enabled: true,
    imageUrl: 'https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg?auto=compress&cs=tinysrgb&w=160&h=600&fit=crop',
    linkUrl: '#',
    width: 160,
    height: 600,
  },
  {
    id: 'sidebar-right',
    label: 'Sağ Panel',
    enabled: true,
    imageUrl: 'https://images.pexels.com/photos/2599244/pexels-photo-2599244.jpeg?auto=compress&cs=tinysrgb&w=160&h=600&fit=crop',
    linkUrl: '#',
    width: 160,
    height: 600,
  },
  {
    id: 'inline',
    label: 'Xəbər içi reklam',
    enabled: true,
    imageUrl: 'https://images.pexels.com/photos/7974/pexels-photo.jpg?auto=compress&cs=tinysrgb&w=728&h=90&fit=crop',
    linkUrl: '#',
    width: 728,
    height: 90,
  },
];

type AdContextType = {
  ads: AdZone[];
  updateAd: (id: string, patch: Partial<AdZone>) => void;
};

const AdContext = createContext<AdContextType>({
  ads: defaultAds,
  updateAd: () => {},
});

export function AdProvider({ children }: { children: React.ReactNode }) {
  const [ads, setAds] = useState<AdZone[]>(defaultAds);

  const updateAd = (id: string, patch: Partial<AdZone>) => {
    setAds((prev) => prev.map((a) => (a.id === id ? { ...a, ...patch } : a)));
  };

  return <AdContext.Provider value={{ ads, updateAd }}>{children}</AdContext.Provider>;
}

export function useAds() {
  return useContext(AdContext);
}

export function useAd(id: string): AdZone | undefined {
  const { ads } = useAds();
  return ads.find((a) => a.id === id);
}
