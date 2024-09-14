import { Box, Center, Loader, Transition, Text } from '@mantine/core';
import { useDebouncedState, useDebouncedValue } from '@mantine/hooks';
import React, { createContext, useState, useContext, ReactNode, useEffect } from 'react';

// Define the context shape
interface LoadingContextType {
  isLoading: boolean;
  setIsLoading: (loading: boolean) => void;
  reason?: string;
  setReason: (reason: string) => void;
}

// Create the context with default values
const LoadingContext = createContext<LoadingContextType | undefined>(undefined);

// Custom hook to use the loading context
export const useLoading = (): LoadingContextType => {
  const context = useContext(LoadingContext);
  if (context === undefined) {
    throw new Error('useLoading must be used within a LoadingProvider');
  }
  return context;
};

// LoadingProvider component to wrap your app
export const LoadingProvider: React.FC<{ children: ReactNode }> = ({ children }) => {
  const [isLoading, setIsLoading] = useDebouncedState<boolean>(false, 1000);
  const [isVisible, setIsVisible] = useState<boolean>(false);
  const [isParentVisible, setIsParentVisible] = useState<boolean>(false);
  const [reason, setReason] = useState<string>('Authentification en cours...');

  useEffect(() => {
    const timeouts: NodeJS.Timeout[] = [];
    if (isLoading) {
      timeouts.push(setTimeout(() => setIsParentVisible(true)));
      timeouts.push(setTimeout(() => setIsVisible(true), 200));
    } else {
      timeouts.push(setTimeout(() => setIsVisible(false), 50));
      timeouts.push(setTimeout(() => setIsParentVisible(false), 150));
      return () => {
        timeouts.forEach((timeout) => {
          clearTimeout(timeout);
        });
      };
    }
  }, [isLoading]);



  return (
    <LoadingContext.Provider value={{ isLoading, setIsLoading, reason, setReason }}>
      {children}
      {isParentVisible && <Center h="100vh" style={{ zIndex: 1000, position: 'fixed', top: 0, left: 0, right: 0, bottom: 0, backgroundColor: 'rgba(0, 0, 0, 0.75)' }}>
        <Transition
          mounted={isVisible}
          transition="pop"
          duration={50}
        >
          {(styles) => (
            <Box style={styles}>
              <Loader size={100} />
              {reason && <Text size="xs">{reason}</Text>}
            </Box>
          )}
        </Transition>
      </Center>
      }
    </LoadingContext.Provider>
  );
};
