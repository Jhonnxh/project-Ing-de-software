-- users: solo identidad y acceso (no mezcles datos académicos aquí)
CREATE TABLE users (
  idUser INT AUTO_INCREMENT PRIMARY KEY,
  fullName VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  passwordHash VARCHAR(255) NOT NULL,
  dni VARCHAR(30) UNIQUE,             -- identidad o número de cuenta (si aplica)
  status ENUM('active','inactive') DEFAULT 'active',
  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- roles: catálogo abierto
CREATE TABLE roles (
  idRole INT AUTO_INCREMENT PRIMARY KEY,
  roleName VARCHAR(50) NOT NULL UNIQUE    -- 'student','teacher','coordinator','head','admin'
);

-- user_roles: un usuario puede tener múltiples roles
CREATE TABLE user_roles (
  idUserRole INT AUTO_INCREMENT PRIMARY KEY,
  idUser INT NOT NULL,
  idRole INT NOT NULL,
  scopeJson JSON NULL,                    -- contexto opcional (p.ej. {"departmentId":5,"campusId":2})
  assignedBy INT NULL,
  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_role (idUser, idRole),
  FOREIGN KEY (idUser) REFERENCES users(idUser),
  FOREIGN KEY (idRole) REFERENCES roles(idRole),
  FOREIGN KEY (assignedBy) REFERENCES users(idUser)
);

-- sessions: soporte SSO con JWT/refresh
CREATE TABLE sessions (
  idSession BIGINT AUTO_INCREMENT PRIMARY KEY,
  idUser INT NOT NULL,
  jwtId CHAR(36) NOT NULL,                -- UUID del JWT (jti)
  refreshTokenHash VARCHAR(255) NOT NULL, -- guarda hash, no el token plano
  userAgent VARCHAR(255) NULL,
  ipAddress VARCHAR(45) NULL,
  expiresAt DATETIME NOT NULL,            -- expiración del refresh
  isRevoked BOOLEAN DEFAULT FALSE,
  createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idUser) REFERENCES users(idUser),
  UNIQUE KEY uq_jwtId (jwtId)
);

-- catálogos mínimos para contexto académico
CREATE TABLE campuses (
  idCampus INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL
);
CREATE TABLE departments (
  idDepartment INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  idCampus INT NOT NULL,
  FOREIGN KEY (idCampus) REFERENCES campuses(idCampus)
);
CREATE TABLE careers (
  idCareer INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  idDepartment INT NOT NULL,
  FOREIGN KEY (idDepartment) REFERENCES departments(idDepartment)
);

-- perfiles
CREATE TABLE student_profiles (
  idStudent INT AUTO_INCREMENT PRIMARY KEY,
  idUser INT NOT NULL UNIQUE,
  accountNumber VARCHAR(30) UNIQUE,        -- número de cuenta
  idCareer INT NOT NULL,
  idCampus INT NOT NULL,
  FOREIGN KEY (idUser) REFERENCES users(idUser),
  FOREIGN KEY (idCareer) REFERENCES careers(idCareer),
  FOREIGN KEY (idCampus) REFERENCES campuses(idCampus)
);

CREATE TABLE teacher_profiles (
  idTeacher INT AUTO_INCREMENT PRIMARY KEY,
  idUser INT NOT NULL UNIQUE,
  employeeNumber VARCHAR(30) UNIQUE,
  idDepartment INT NOT NULL,
  idCampus INT NOT NULL,
  photoUrl VARCHAR(255) NULL,
  FOREIGN KEY (idUser) REFERENCES users(idUser),
  FOREIGN KEY (idDepartment) REFERENCES departments(idDepartment),
  FOREIGN KEY (idCampus) REFERENCES campuses(idCampus)
);
--Un coordinator o head es también docente (según las especificaciones). No necesitas tablas nuevas:
-- asignas el rol adicional en user_roles y pones su scope (dep./campus) en scopeJson.

--Permisos por rol y módulos
CREATE TABLE permissions (
  idPermission INT AUTO_INCREMENT PRIMARY KEY,
  permKey VARCHAR(100) NOT NULL UNIQUE     -- e.g. 'admissions.review','enrollment.open','library.upload'
);

CREATE TABLE role_permissions (
  idRole INT NOT NULL,
  idPermission INT NOT NULL,
  PRIMARY KEY (idRole, idPermission),
  FOREIGN KEY (idRole) REFERENCES roles(idRole),
  FOREIGN KEY (idPermission) REFERENCES permissions(idPermission)
);
