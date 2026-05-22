# Clean Architecture trong React.js - Hướng dẫn cho Senior Frontend Developer

## 📚 Mục Lục
1. [Giới thiệu](#giới-thiệu)
2. [Nguyên tắc cơ bản](#nguyên-tắc-cơ-bản)
3. [Cấu trúc thư mục](#cấu-trúc-thư-mục)
4. [Các Layer trong Clean Architecture](#các-layer)
5. [Ví dụ thực tế](#ví-dụ-thực-tế)
6. [State Management Patterns](#state-management-patterns)
7. [Performance & Optimization](#performance--optimization)
8. [Testing Strategy](#testing-strategy)
9. [Best Practices](#best-practices)

---

## 🎯 Giới thiệu

**Clean Architecture** là một cách tổ chức code để tạo ra các ứng dụng dễ bảo trì, dễ test và không phụ thuộc vào framework cụ thể.

### Lợi ích:
- ✅ Độc lập với framework/thư viện
- ✅ Dễ kiểm thử (testing)
- ✅ Dễ mở rộng và bảo trì
- ✅ Tách biệt mối quan tâm (Separation of Concerns)
- ✅ Giảm phức tạp

---

## 🏗️ Nguyên tắc Cơ bản

### Dependency Rule (Quy tắc Phụ thuộc)
```
Outer layers → Inner layers (allowed)
Inner layers → Outer layers (NOT allowed)
```

Các layer trong cuốn bên ngoài **không biết** về các chi tiết của layer bên trong.

---

## 📁 Cấu trúc Thư mục

```
src/
├── core/                    # Lõi của ứng dụng (không phụ thuộc vào UI)
│   ├── entities/           # Các đối tượng dữ liệu chung
│   ├── failures/           # Xử lý lỗi
│   └── usecases/           # Các trường hợp sử dụng
│
├── data/                   # Lớp dữ liệu (Repositories, APIs, Databases)
│   ├── datasources/        # API clients, Database handlers
│   ├── models/             # Dữ liệu thô từ server
│   ├── repositories/       # Triển khai của Repository interface
│   └── mappers/            # Chuyển đổi giữa Model và Entity
│
├── presentation/           # Lớp giao diện (Components, Pages, Redux/Zustand)
│   ├── pages/             # Trang chính
│   ├── components/        # Các component tái sử dụng
│   ├── hooks/             # Custom hooks
│   ├── state/             # State management (Redux, Zustand, etc)
│   └── styles/            # CSS/Styled components
│
└── config/                 # Cấu hình toàn cục
    ├── api.ts
    └── constants.ts
```

---

## 🏛️ Các Layer Trong Clean Architecture

### 1. **Core Layer (Entity & Use Cases)**

**Entities** - Đối tượng dữ liệu pure:

```typescript
// src/core/entities/user.ts
export interface User {
  id: string;
  name: string;
  email: string;
  createdAt: Date;
}
```

**Use Cases** - Logic kinh doanh (Business Logic):

```typescript
// src/core/usecases/get_users_usecase.ts
import { User } from '../entities/user';

export interface GetUsersRepository {
  getUsers(): Promise<User[]>;
}

export class GetUsersUseCase {
  constructor(private repository: GetUsersRepository) {}

  async call(): Promise<User[]> {
    const users = await this.repository.getUsers();
    // Có thể thêm logic xử lý nếu cần
    return users.filter(user => user.email); // Ví dụ: lọc user hợp lệ
  }
}
```

**Failure/Error Handling**:

```typescript
// src/core/failures/failure.ts
export abstract class Failure {
  final message: string;
}

export class ServerFailure extends Failure {
  final message = 'Server error occurred';
}

export class NetworkFailure extends Failure {
  final message = 'No internet connection';
}
```

---

### 2. **Data Layer (Repository & DataSources)**

**Repository Pattern** - Trừu tượng hóa nguồn dữ liệu:

```typescript
// src/data/repositories/user_repository.ts
import { User } from '../../core/entities/user';
import { GetUsersRepository } from '../../core/usecases/get_users_usecase';
import { UserRemoteDataSource } from '../datasources/user_remote_datasource';

export class UserRepository implements GetUsersRepository {
  constructor(private remoteDataSource: UserRemoteDataSource) {}

  async getUsers(): Promise<User[]> {
    try {
      return await this.remoteDataSource.getUsers();
    } catch (error) {
      throw error;
    }
  }
}
```

**DataSource** - Kết nối API/Database:

```typescript
// src/data/datasources/user_remote_datasource.ts
import axios from 'axios';
import { User } from '../../core/entities/user';
import { UserModel } from '../models/user_model';

export class UserRemoteDataSource {
  constructor(private apiUrl: string) {}

  async getUsers(): Promise<User[]> {
    const response = await axios.get<UserModel[]>(`${this.apiUrl}/users`);
    return response.data.map(model => this.mapModelToEntity(model));
  }

  private mapModelToEntity(model: UserModel): User {
    return {
      id: model.id,
      name: model.name,
      email: model.email,
      createdAt: new Date(model.createdAt),
    };
  }
}
```

**Model** - Dữ liệu thô từ server:

```typescript
// src/data/models/user_model.ts
export interface UserModel {
  id: string;
  name: string;
  email: string;
  createdAt: string; // JSON trả về dạng string
}
```

---

### 3. **Presentation Layer (UI Components & State Management)**

**Custom Hook** - Kết nối Use Case với Component:

```typescript
// src/presentation/hooks/useGetUsers.ts
import { useState, useEffect } from 'react';
import { User } from '../../core/entities/user';
import { GetUsersUseCase } from '../../core/usecases/get_users_usecase';
import { Failure } from '../../core/failures/failure';

interface UseGetUsersResult {
  users: User[];
  loading: boolean;
  error: Failure | null;
}

export const useGetUsers = (useCase: GetUsersUseCase): UseGetUsersResult => {
  const [users, setUsers] = useState<User[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<Failure | null>(null);

  useEffect(() => {
    const fetchUsers = async () => {
      setLoading(true);
      try {
        const result = await useCase.call();
        setUsers(result);
        setError(null);
      } catch (err) {
        setError(err as Failure);
      } finally {
        setLoading(false);
      }
    };

    fetchUsers();
  }, [useCase]);

  return { users, loading, error };
};
```

**Page Component** - Giao diện chính:

```typescript
// src/presentation/pages/users_page.tsx
import React from 'react';
import { useGetUsers } from '../hooks/useGetUsers';
import { getUsersUseCase } from '../../config/service_locator';

export const UsersPage: React.FC = () => {
  const { users, loading, error } = useGetUsers(getUsersUseCase);

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error.message}</div>;

  return (
    <div className="users-container">
      <h1>Users List</h1>
      <ul>
        {users.map(user => (
          <li key={user.id}>
            {user.name} ({user.email})
          </li>
        ))}
      </ul>
    </div>
  );
};
```

**Component** - Tái sử dụng:

```typescript
// src/presentation/components/user_card.tsx
import React from 'react';
import { User } from '../../core/entities/user';

interface UserCardProps {
  user: User;
  onClick?: (user: User) => void;
}

export const UserCard: React.FC<UserCardProps> = ({ user, onClick }) => {
  return (
    <div 
      className="user-card"
      onClick={() => onClick?.(user)}
    >
      <h3>{user.name}</h3>
      <p>{user.email}</p>
      <small>{user.createdAt.toLocaleDateString()}</small>
    </div>
  );
};
```

---

## 💡 Ví dụ Thực tế - Tạo API Fetch

### Bước 1: Định nghĩa Entity (Core)

```typescript
// src/core/entities/product.ts
export interface Product {
  id: string;
  title: string;
  price: number;
  description: string;
}
```

### Bước 2: Tạo Use Case (Core)

```typescript
// src/core/usecases/get_products_usecase.ts
import { Product } from '../entities/product';

export interface GetProductsRepository {
  getProducts(category: string): Promise<Product[]>;
  getProductById(id: string): Promise<Product>;
}

export class GetProductsUseCase {
  constructor(private repository: GetProductsRepository) {}

  async execute(category: string): Promise<Product[]> {
    if (!category) throw new Error('Category is required');
    return await this.repository.getProducts(category);
  }
}

export class GetProductByIdUseCase {
  constructor(private repository: GetProductsRepository) {}

  async execute(id: string): Promise<Product> {
    if (!id) throw new Error('Product ID is required');
    return await this.repository.getProductById(id);
  }
}
```

### Bước 3: Tạo DataSource (Data)

```typescript
// src/data/datasources/product_api_datasource.ts
import axios from 'axios';
import { Product } from '../../core/entities/product';

const API_BASE_URL = 'https://api.example.com';

export class ProductApiDataSource {
  async getProducts(category: string): Promise<Product[]> {
    const response = await axios.get<Product[]>(
      `${API_BASE_URL}/products?category=${category}`
    );
    return response.data;
  }

  async getProductById(id: string): Promise<Product> {
    const response = await axios.get<Product>(
      `${API_BASE_URL}/products/${id}`
    );
    return response.data;
  }
}
```

### Bước 4: Tạo Repository (Data)

```typescript
// src/data/repositories/product_repository.ts
import { Product } from '../../core/entities/product';
import { 
  GetProductsRepository 
} from '../../core/usecases/get_products_usecase';
import { ProductApiDataSource } from '../datasources/product_api_datasource';

export class ProductRepository implements GetProductsRepository {
  constructor(private dataSource: ProductApiDataSource) {}

  async getProducts(category: string): Promise<Product[]> {
    return await this.dataSource.getProducts(category);
  }

  async getProductById(id: string): Promise<Product> {
    return await this.dataSource.getProductById(id);
  }
}
```

### Bước 5: Tạo Service Locator (Config)

```typescript
// src/config/service_locator.ts
import { ProductApiDataSource } from '../data/datasources/product_api_datasource';
import { ProductRepository } from '../data/repositories/product_repository';
import { 
  GetProductsUseCase, 
  GetProductByIdUseCase 
} from '../core/usecases/get_products_usecase';

// Khởi tạo các dependencies
const productApiDataSource = new ProductApiDataSource();
const productRepository = new ProductRepository(productApiDataSource);

export const getProductsUseCase = new GetProductsUseCase(productRepository);
export const getProductByIdUseCase = new GetProductByIdUseCase(productRepository);
```

### Bước 6: Sử dụng trong Component (Presentation)

```typescript
// src/presentation/pages/products_page.tsx
import React, { useState, useEffect } from 'react';
import { Product } from '../../core/entities/product';
import { GetProductsUseCase } from '../../core/usecases/get_products_usecase';
import { getProductsUseCase } from '../../config/service_locator';
import { ProductCard } from '../components/product_card';

interface ProductsPageProps {
  initialCategory?: string;
}

export const ProductsPage: React.FC<ProductsPageProps> = ({ 
  initialCategory = 'electronics' 
}) => {
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [selectedCategory, setSelectedCategory] = useState(initialCategory);

  useEffect(() => {
    const fetchProducts = async () => {
      setLoading(true);
      setError(null);
      try {
        const result = await getProductsUseCase.execute(selectedCategory);
        setProducts(result);
      } catch (err) {
        setError((err as Error).message);
      } finally {
        setLoading(false);
      }
    };

    fetchProducts();
  }, [selectedCategory]);

  return (
    <div className="products-page">
      <h1>Products</h1>
      
      <div className="category-selector">
        <select 
          value={selectedCategory}
          onChange={(e) => setSelectedCategory(e.target.value)}
        >
          <option value="electronics">Electronics</option>
          <option value="clothing">Clothing</option>
          <option value="books">Books</option>
        </select>
      </div>

      {loading && <p>Loading products...</p>}
      {error && <p className="error">{error}</p>}

      <div className="products-grid">
        {products.map(product => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>
    </div>
  );
};
```

---

## ✅ Best Practices cho Senior Frontend Developer

### 1. **Typescript Strict Mode**

```typescript
// tsconfig.json
{
  "compilerOptions": {
    "strict": true,
    "noImplicitAny": true,
    "strictNullChecks": true,
    "strictFunctionTypes": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noImplicitReturns": true,
    "noFallthroughCasesInSwitch": true,
    "esModuleInterop": true
  }
}
```

### 2. **Type-Safe API Responses**

```typescript
// ❌ Xấu - Dùng any
const response: any = await fetch('/api/products');

// ✅ Tốt - Type-safe
interface ApiResponse<T> {
  data: T;
  error?: string;
  status: number;
}

const response: ApiResponse<Product[]> = await fetch('/api/products');
```

### 3. **Error Boundaries & Fallbacks**

```typescript
// src/presentation/components/ErrorBoundary.tsx
import React from 'react';

interface Props {
  children: React.ReactNode;
  fallback?: React.ReactNode;
}

interface State {
  hasError: boolean;
  error: Error | null;
}

export class ErrorBoundary extends React.Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { hasError: false, error: null };
  }

  static getDerivedStateFromError(error: Error) {
    return { hasError: true, error };
  }

  componentDidCatch(error: Error, errorInfo: React.ErrorInfo) {
    console.error('Error caught:', error, errorInfo);
  }

  render() {
    if (this.state.hasError) {
      return this.props.fallback || <div>Something went wrong</div>;
    }

    return this.props.children;
  }
}
```

### 4. **Custom Hooks for Logic Reuse**

```typescript
// src/presentation/hooks/useFetch.ts
interface UseFetchOptions<T> {
  onSuccess?: (data: T) => void;
  onError?: (error: Error) => void;
  dependencies?: React.DependencyList;
}

export const useFetch = <T,>(
  fetcher: () => Promise<T>,
  options: UseFetchOptions<T> = {}
) => {
  const [data, setData] = React.useState<T | null>(null);
  const [loading, setLoading] = React.useState(false);
  const [error, setError] = React.useState<Error | null>(null);

  React.useEffect(() => {
    let isMounted = true;

    const fetchData = async () => {
      setLoading(true);
      try {
        const result = await fetcher();
        if (isMounted) {
          setData(result);
          options.onSuccess?.(result);
        }
      } catch (err) {
        if (isMounted) {
          const error = err instanceof Error ? err : new Error(String(err));
          setError(error);
          options.onError?.(error);
        }
      } finally {
        if (isMounted) {
          setLoading(false);
        }
      }
    };

    fetchData();

    return () => {
      isMounted = false;
    };
  }, options.dependencies);

  return { data, loading, error };
};
```

### 5. **Form Management với Validation**

```typescript
// src/presentation/hooks/useForm.ts
interface UseFormOptions<T> {
  initialValues: T;
  onSubmit: (values: T) => Promise<void>;
  validate?: (values: T) => Record<string, string>;
}

export const useForm = <T extends Record<string, any>>({
  initialValues,
  onSubmit,
  validate,
}: UseFormOptions<T>) => {
  const [values, setValues] = React.useState(initialValues);
  const [errors, setErrors] = React.useState<Record<string, string>>({});
  const [touched, setTouched] = React.useState<Record<string, boolean>>({});
  const [isSubmitting, setIsSubmitting] = React.useState(false);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value, type, checked } = e.target;
    setValues(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleBlur = (e: React.FocusEvent<HTMLInputElement>) => {
    const { name } = e.target;
    setTouched(prev => ({ ...prev, [name]: true }));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    if (validate) {
      const newErrors = validate(values);
      setErrors(newErrors);
      if (Object.keys(newErrors).length > 0) return;
    }

    setIsSubmitting(true);
    try {
      await onSubmit(values);
    } finally {
      setIsSubmitting(false);
    }
  };

  return {
    values,
    errors,
    touched,
    isSubmitting,
    handleChange,
    handleBlur,
    handleSubmit,
    setValues,
  };
};
```

### 6. **Environment Variables & Configuration**

```typescript
// src/config/environment.ts
interface EnvironmentConfig {
  apiUrl: string;
  apiTimeout: number;
  environment: 'development' | 'production' | 'staging';
  enableLogging: boolean;
}

const getEnvironmentConfig = (): EnvironmentConfig => {
  const env = process.env.REACT_APP_ENV || 'development';

  const config: Record<string, EnvironmentConfig> = {
    development: {
      apiUrl: 'http://localhost:3001',
      apiTimeout: 10000,
      environment: 'development',
      enableLogging: true,
    },
    production: {
      apiUrl: 'https://api.example.com',
      apiTimeout: 5000,
      environment: 'production',
      enableLogging: false,
    },
    staging: {
      apiUrl: 'https://api-staging.example.com',
      apiTimeout: 5000,
      environment: 'staging',
      enableLogging: true,
    },
  };

  return config[env];
};

export const config = getEnvironmentConfig();
```

### 7. **Logger Utility**

```typescript
// src/config/logger.ts
export const logger = {
  log: (message: string, data?: any) => {
    if (config.enableLogging) {
      console.log(`[${new Date().toISOString()}] ${message}`, data);
    }
  },
  error: (message: string, error?: Error) => {
    console.error(`[ERROR] ${message}`, error);
  },
  warn: (message: string, data?: any) => {
    console.warn(`[WARN] ${message}`, data);
  },
};
```

### 8. **Avoid Props Drilling - Use Composition**

```typescript
// ❌ Props drilling
const App = () => (
  <Page1 user={user} onUserChange={onUserChange} />
);

const Page1 = ({ user, onUserChange }) => (
  <Page2 user={user} onUserChange={onUserChange} />
);

// ✅ Better - Use Context
const UserProvider = ({ children }) => (
  <UserContext.Provider value={{ user, onUserChange }}>
    {children}
  </UserContext.Provider>
);

const App = () => (
  <UserProvider>
    <Page1 />
  </UserProvider>
);
```

### 9. **Consistent File Structure**

```
src/
├── core/
│   ├── entities/
│   ├── usecases/
│   └── failures/
├── data/
│   ├── datasources/
│   ├── models/
│   ├── mappers/
│   └── repositories/
├── presentation/
│   ├── pages/
│   ├── components/
│   ├── hooks/
│   ├── store/
│   └── styles/
├── config/
│   ├── environment.ts
│   ├── service_locator.ts
│   ├── logger.ts
│   └── constants.ts
└── App.tsx
```

### 10. **Git Commit Convention**

```
feat: Add product listing feature
fix: Fix memory leak in useEffect hook
refactor: Extract validation logic into Use Case
test: Add tests for ProductRepository
docs: Update Clean Architecture guide
chore: Update dependencies
```

---

## 🔧 Stack Công nghệ Khuyên dùng (2024)

| Chức năng | Công nghệ | Ghi chú |
|----------|-----------|--------|
| Framework | React 19+ | TypeScript bắt buộc |
| HTTP Client | TanStack Query + Fetch API | Thay thế axios |
| State Management | Zustand / TanStack Query | Không dùng Redux nữa |
| Type Safety | TypeScript strict mode | Tuple types, generics |
| Styling | Tailwind CSS v3+ | Utility-first CSS |
| Component Library | Shadcn/ui hoặc Radix UI | Headless & customizable |
| Testing | Vitest + React Testing Library | Thay thế Jest |
| Linting | ESLint + Prettier | Consistent code format |
| Build Tool | Vite | Thay thế Create React App |
| E2E Testing | Playwright hoặc Cypress | Browser automation |
| API Mocking | MSW (Mock Service Worker) | Intercept requests |
| Bundle Analysis | Source Map Explorer | Monitor bundle size |

---

## 📋 Folder Structure Advanced Pattern

### Feature-Based Architecture

```
src/
├── features/
│   ├── products/
│   │   ├── core/
│   │   │   ├── entities/
│   │   │   ├── usecases/
│   │   │   └── failures/
│   │   ├── data/
│   │   │   ├── datasources/
│   │   │   ├── models/
│   │   │   └── repositories/
│   │   ├── presentation/
│   │   │   ├── pages/
│   │   │   ├── components/
│   │   │   ├── hooks/
│   │   │   └── store/
│   │   └── index.ts (barrel export)
│   │
│   ├── auth/
│   │   ├── core/
│   │   ├── data/
│   │   ├── presentation/
│   │   └── index.ts
│   │
│   └── user-profile/
│       ├── core/
│       ├── data/
│       ├── presentation/
│       └── index.ts
│
├── shared/
│   ├── components/
│   ├── hooks/
│   ├── utils/
│   ├── types/
│   └── constants/
│
├── config/
└── App.tsx
```

**Lợi ích:**
- Dễ scale lên khi project lớn
- Mỗi feature độc lập
- Dễ xóa feature nếu cần
- Team có thể làm việc song song

---

## ✨ Performance Checklist

- [ ] Enable code splitting cho routes
- [ ] Implement virtual scrolling cho large lists
- [ ] Use React.memo cho expensive components
- [ ] Optimize images (lazy load, srcset)
- [ ] Implement request deduplication (React Query)
- [ ] Use TanStack Query for caching
- [ ] Bundle analysis: `npm run build -- --analyze`
- [ ] Lighthouse score > 90
- [ ] Core Web Vitals: LCP, FID, CLS
- [ ] Tree shake unused code
- [ ] Minify CSS & JS
- [ ] Enable gzip compression
- [ ] Monitor memory leaks
- [ ] Profile components với DevTools

---

## 🚀 Deployment Checklist

- [ ] Environment variables configured
- [ ] Error logging setup (Sentry, etc)
- [ ] Performance monitoring (Google Analytics, Datadog)
- [ ] Security headers configured
- [ ] CORS properly setup
- [ ] SSL certificate valid
- [ ] Database backups automated
- [ ] CI/CD pipeline configured
- [ ] Rollback plan in place
- [ ] Load testing done
- [ ] Security audit passed
- [ ] Documentation updated

---

## 🎯 State Management Patterns

### Pattern 1: Context API + Custom Hooks (Lightweight)

Phù hợp cho các ứng dụng nhỏ đến vừa:

```typescript
// src/presentation/context/ProductContext.tsx
import React, { createContext, useReducer, useCallback } from 'react';
import { Product } from '../../core/entities/product';

interface ProductState {
  products: Product[];
  loading: boolean;
  error: string | null;
}

interface ProductContextType extends ProductState {
  fetchProducts: (category: string) => Promise<void>;
  clearError: () => void;
}

export const ProductContext = createContext<ProductContextType | undefined>(undefined);

type ProductAction =
  | { type: 'LOADING' }
  | { type: 'SUCCESS'; payload: Product[] }
  | { type: 'ERROR'; payload: string }
  | { type: 'CLEAR_ERROR' };

const initialState: ProductState = {
  products: [],
  loading: false,
  error: null,
};

const reducer = (state: ProductState, action: ProductAction): ProductState => {
  switch (action.type) {
    case 'LOADING':
      return { ...state, loading: true, error: null };
    case 'SUCCESS':
      return { ...state, loading: false, products: action.payload };
    case 'ERROR':
      return { ...state, loading: false, error: action.payload };
    case 'CLEAR_ERROR':
      return { ...state, error: null };
    default:
      return state;
  }
};

export const ProductProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [state, dispatch] = useReducer(reducer, initialState);
  const { getProductsUseCase } = require('../../config/service_locator');

  const fetchProducts = useCallback(async (category: string) => {
    dispatch({ type: 'LOADING' });
    try {
      const products = await getProductsUseCase.execute(category);
      dispatch({ type: 'SUCCESS', payload: products });
    } catch (error) {
      dispatch({ type: 'ERROR', payload: (error as Error).message });
    }
  }, []);

  const clearError = useCallback(() => {
    dispatch({ type: 'CLEAR_ERROR' });
  }, []);

  return (
    <ProductContext.Provider value={{ ...state, fetchProducts, clearError }}>
      {children}
    </ProductContext.Provider>
  );
};

export const useProductContext = () => {
  const context = React.useContext(ProductContext);
  if (!context) {
    throw new Error('useProductContext must be used within ProductProvider');
  }
  return context;
};
```

### Pattern 2: Zustand (Recommended for Medium to Large Apps)

```typescript
// src/presentation/store/productStore.ts
import create from 'zustand';
import { devtools, persist } from 'zustand/middleware';
import { Product } from '../../core/entities/product';
import { getProductsUseCase } from '../../config/service_locator';

interface ProductStore {
  products: Product[];
  loading: boolean;
  error: string | null;
  selectedCategory: string;
  
  // Actions
  setProducts: (products: Product[]) => void;
  setLoading: (loading: boolean) => void;
  setError: (error: string | null) => void;
  setSelectedCategory: (category: string) => void;
  fetchProducts: (category: string) => Promise<void>;
  resetStore: () => void;
}

export const useProductStore = create<ProductStore>()(
  devtools(
    persist(
      (set) => ({
        products: [],
        loading: false,
        error: null,
        selectedCategory: 'electronics',

        setProducts: (products) => set({ products }),
        setLoading: (loading) => set({ loading }),
        setError: (error) => set({ error }),
        setSelectedCategory: (category) => set({ selectedCategory: category }),

        fetchProducts: async (category: string) => {
          set({ loading: true, error: null });
          try {
            const products = await getProductsUseCase.execute(category);
            set({ products, loading: false });
          } catch (error) {
            set({
              error: (error as Error).message,
              loading: false,
            });
          }
        },

        resetStore: () =>
          set({
            products: [],
            loading: false,
            error: null,
            selectedCategory: 'electronics',
          }),
      }),
      {
        name: 'product-storage', // Persist to localStorage
      }
    ),
    { name: 'ProductStore' }
  )
);
```

Sử dụng trong Component:

```typescript
import { useProductStore } from '../store/productStore';

export const ProductsPage = () => {
  const { products, loading, error, fetchProducts } = useProductStore();

  useEffect(() => {
    fetchProducts('electronics');
  }, [fetchProducts]);

  return (
    // Component JSX
  );
};
```

### Pattern 3: TanStack Query (React Query)

Tốt nhất cho caching và synchronization dữ liệu:

```typescript
// src/presentation/hooks/useProductsQuery.ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { getProductsUseCase, getProductByIdUseCase } from '../../config/service_locator';

export const useProductsQuery = (category: string) => {
  return useQuery({
    queryKey: ['products', category],
    queryFn: () => getProductsUseCase.execute(category),
    staleTime: 1000 * 60 * 5, // 5 minutes
    gcTime: 1000 * 60 * 10, // 10 minutes (formerly cacheTime)
  });
};

export const useProductByIdQuery = (id: string) => {
  return useQuery({
    queryKey: ['product', id],
    queryFn: () => getProductByIdUseCase.execute(id),
    enabled: !!id, // Only run query if id exists
  });
};

// Mutations
export const useCreateProductMutation = () => {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (newProduct: Omit<Product, 'id'>) => {
      // Call your useCase here
      return newProduct;
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['products'] });
    },
  });
};
```

Sử dụng:

```typescript
export const ProductsPage = ({ category }: { category: string }) => {
  const { data: products, isLoading, error } = useProductsQuery(category);
  const createMutation = useCreateProductMutation();

  if (isLoading) return <Skeleton />;
  if (error) return <ErrorBoundary error={error} />;

  return (
    <div>
      {products?.map(product => (
        <ProductCard key={product.id} product={product} />
      ))}
    </div>
  );
};
```

---

## ⚡ Performance & Optimization

### 1. Memoization Patterns

```typescript
// src/presentation/components/ProductCard.tsx
import { memo, useMemo, useCallback } from 'react';
import { Product } from '../../core/entities/product';

interface ProductCardProps {
  product: Product;
  onSelect: (product: Product) => void;
}

export const ProductCard = memo(({ product, onSelect }: ProductCardProps) => {
  // useMemo cho expensive calculations
  const discountedPrice = useMemo(() => {
    console.log('Calculating discounted price...');
    return product.price * 0.9;
  }, [product.price]);

  // useCallback để giữ function reference
  const handleClick = useCallback(() => {
    onSelect(product);
  }, [product, onSelect]);

  return (
    <div onClick={handleClick}>
      <h3>{product.title}</h3>
      <p>${discountedPrice.toFixed(2)}</p>
    </div>
  );
});

ProductCard.displayName = 'ProductCard';
```

### 2. Code Splitting & Lazy Loading

```typescript
// src/presentation/pages/index.ts
import { lazy } from 'react';

export const HomePage = lazy(() =>
  import('./home_page').then(module => ({
    default: module.HomePage,
  }))
);

export const ProductsPage = lazy(() =>
  import('./products_page').then(module => ({
    default: module.ProductsPage,
  }))
);

// src/App.tsx
import { Suspense } from 'react';
import { HomePage, ProductsPage } from './presentation/pages';

export const App = () => {
  return (
    <Suspense fallback={<LoadingSpinner />}>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route path="/products" element={<ProductsPage />} />
      </Routes>
    </Suspense>
  );
};
```

### 3. Virtual Scrolling (Large Lists)

```typescript
// src/presentation/components/VirtualizedProductList.tsx
import { FixedSizeList } from 'react-window';
import { Product } from '../../core/entities/product';

interface VirtualizedListProps {
  products: Product[];
  height: number;
  width: number;
}

export const VirtualizedProductList = ({
  products,
  height,
  width,
}: VirtualizedListProps) => {
  const Row = ({ index, style }: { index: number; style: React.CSSProperties }) => (
    <div style={style}>
      <ProductCard product={products[index]} />
    </div>
  );

  return (
    <FixedSizeList
      height={height}
      itemCount={products.length}
      itemSize={100}
      width={width}
    >
      {Row}
    </FixedSizeList>
  );
};
```

### 4. Image Optimization

```typescript
// src/presentation/components/OptimizedImage.tsx
export const OptimizedImage = ({ src, alt }: { src: string; alt: string }) => {
  return (
    <img
      src={src}
      alt={alt}
      loading="lazy"
      decoding="async"
      sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
      srcSet={`
        ${src}?w=640 640w,
        ${src}?w=1024 1024w,
        ${src}?w=1920 1920w
      `}
    />
  );
};
```

---

## 🧪 Testing Strategy

### Unit Tests - Use Cases

```typescript
// src/core/usecases/__tests__/get_products_usecase.test.ts
import { GetProductsUseCase, GetProductsRepository } from '../get_products_usecase';
import { Product } from '../../entities/product';

describe('GetProductsUseCase', () => {
  let mockRepository: jest.Mocked<GetProductsRepository>;
  let useCase: GetProductsUseCase;

  beforeEach(() => {
    mockRepository = {
      getProducts: jest.fn(),
      getProductById: jest.fn(),
    };
    useCase = new GetProductsUseCase(mockRepository);
  });

  it('should fetch products successfully', async () => {
    const mockProducts: Product[] = [
      { id: '1', title: 'Product 1', price: 100, description: 'Desc 1' },
      { id: '2', title: 'Product 2', price: 200, description: 'Desc 2' },
    ];

    mockRepository.getProducts.mockResolvedValue(mockProducts);

    const result = await useCase.execute('electronics');

    expect(result).toEqual(mockProducts);
    expect(mockRepository.getProducts).toHaveBeenCalledWith('electronics');
  });

  it('should throw error when category is invalid', async () => {
    await expect(useCase.execute('')).rejects.toThrow('Category is required');
  });

  it('should handle repository errors', async () => {
    const error = new Error('Network error');
    mockRepository.getProducts.mockRejectedValue(error);

    await expect(useCase.execute('electronics')).rejects.toThrow('Network error');
  });
});
```

### Component Tests - React Testing Library

```typescript
// src/presentation/pages/__tests__/products_page.test.tsx
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { ProductsPage } from '../products_page';
import * as serviceLocator from '../../../config/service_locator';

jest.mock('../../../config/service_locator');

describe('ProductsPage', () => {
  it('should display loading state initially', () => {
    const mockUseCase = {
      execute: jest.fn().mockImplementation(() => new Promise(() => {})),
    };

    (serviceLocator.getProductsUseCase as jest.Mock).mockReturnValue(mockUseCase);

    render(<ProductsPage />);
    expect(screen.getByText(/loading/i)).toBeInTheDocument();
  });

  it('should display products after fetching', async () => {
    const mockProducts = [
      { id: '1', title: 'Product 1', price: 100, description: 'Desc 1' },
    ];

    const mockUseCase = {
      execute: jest.fn().mockResolvedValue(mockProducts),
    };

    (serviceLocator.getProductsUseCase as jest.Mock).mockReturnValue(mockUseCase);

    render(<ProductsPage />);

    await waitFor(() => {
      expect(screen.getByText('Product 1')).toBeInTheDocument();
    });
  });

  it('should handle category change', async () => {
    const user = userEvent.setup();
    const mockUseCase = {
      execute: jest.fn().mockResolvedValue([]),
    };

    (serviceLocator.getProductsUseCase as jest.Mock).mockReturnValue(mockUseCase);

    render(<ProductsPage />);

    const selectElement = screen.getByDisplayValue('electronics');
    await user.selectOptions(selectElement, 'clothing');

    await waitFor(() => {
      expect(mockUseCase.execute).toHaveBeenCalledWith('clothing');
    });
  });
});
```

### Integration Tests - MSW (Mock Service Worker)

```typescript
// src/presentation/__tests__/setup.ts
import { setupServer } from 'msw/node';
import { http, HttpResponse } from 'msw';

const handlers = [
  http.get('https://api.example.com/products', ({ request }) => {
    const url = new URL(request.url);
    const category = url.searchParams.get('category');

    if (category === 'electronics') {
      return HttpResponse.json([
        { id: '1', title: 'Laptop', price: 1000, description: 'High-end laptop' },
      ]);
    }

    return HttpResponse.json([]);
  }),
];

export const server = setupServer(...handlers);
```

## 📚 Tài liệu Tham khảo & Courses

### Kinh Điển
- [Clean Architecture by Robert C. Martin](https://blog.cleancoder.com/uncle-bob/2012/08/13/the-clean-architecture.html)
- [Domain-Driven Design - Eric Evans](https://en.wikipedia.org/wiki/Domain-driven_design)
- [The Pragmatic Programmer](https://pragprog.com/titles/tpp20/the-pragmatic-programmer-20th-anniversary-edition/)

### React & Modern Frontend
- [React Documentation](https://react.dev)
- [TypeScript Handbook](https://www.typescriptlang.org/docs/)
- [TanStack Query (React Query)](https://tanstack.com/query/latest)
- [Zustand State Management](https://zustand-demo.vercel.app/)
- [Vite Documentation](https://vitejs.dev)

### Advanced Patterns
- [Epic React by Kent C. Dodds](https://epicreact.dev/)
- [Advanced React Patterns](https://www.patterns.dev/posts/container-component-pattern/)
- [Composition Over Inheritance](https://www.robinwieruch.de/react-composition/)

### Testing
- [Testing Library Best Practices](https://testing-library.com/docs/)
- [Vitest Documentation](https://vitest.dev/)
- [MSW (Mock Service Worker)](https://mswjs.io/)

### Performance
- [Web Vitals - Google](https://web.dev/vitals/)
- [React Performance Optimization](https://react.dev/learn/render-and-commit)
- [Lighthouse](https://developers.google.com/web/tools/lighthouse)

### Tools & Ecosystem
- [Shadcn/ui Components](https://ui.shadcn.com/)
- [Tailwind CSS](https://tailwindcss.com/)
- [ESLint & Prettier](https://prettier.io/)

---

## 🎯 Lộ Trình Học Tập (Learning Path)

### Tuần 1-2: Foundations
- [ ] Hiểu Clean Architecture principles
- [ ] Setup project structure
- [ ] Tạo Entity & Use Cases đầu tiên
- [ ] Viết unit tests cho Use Cases

### Tuần 3-4: Data Layer
- [ ] Tạo Repository pattern
- [ ] Implement DataSource (API calls)
- [ ] Mapper - chuyển đổi giữa Model & Entity
- [ ] Error handling strategy

### Tuần 5-6: Presentation Layer
- [ ] Tạo custom hooks kết nối Use Cases
- [ ] Build UI components
- [ ] State management setup
- [ ] Form handling & validation

### Tuần 7-8: Advanced
- [ ] Performance optimization
- [ ] Code splitting & lazy loading
- [ ] Integration tests
- [ ] Deployment setup

---

## 📞 Common Issues & Solutions

### Issue 1: Circular Dependencies
```typescript
// ❌ Circular dependency
// file-a.ts imports file-b.ts
// file-b.ts imports file-a.ts

// ✅ Solution: Use dependency injection or create a third file
// factory.ts handles the dependency
```

### Issue 2: Props Drilling
```typescript
// ✅ Solution: Use Context API or State Management
const UserContext = createContext();
```

### Issue 3: Memory Leaks in Hooks
```typescript
// ✅ Solution: Cleanup function
useEffect(() => {
  const subscription = subscribe();
  return () => subscription.unsubscribe();
}, []);
```

### Issue 4: Stale Closures
```typescript
// ✅ Solution: Add dependencies to useEffect
useEffect(() => {
  // ...
}, [dependency]); // Don't forget dependencies
```

---

## 🎓 Kết Luận

Clean Architecture không phải về Perfect Code, mà về:
- ✅ **Maintainability** - Dễ bảo trì và mở rộng
- ✅ **Testability** - Dễ viết test
- ✅ **Flexibility** - Dễ thay đổi implementation
- ✅ **Collaboration** - Team dễ làm việc cùng nhau
- ✅ **Scalability** - Dễ scale project

**Quan trọng nhất:** Áp dụng nguyên lý một cách hợp lý, không quá cứng nhắc!

---

**Happy Coding! 🚀**

*Last Updated: 2024*
*Maintained by: Senior Frontend Developer Community*
