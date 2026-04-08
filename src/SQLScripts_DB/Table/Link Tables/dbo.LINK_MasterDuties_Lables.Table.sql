USE [Allocate7]
GO
/****** Object:  Table [dbo].[LINK_MasterDuties_Lables]    Script Date: 30/09/2021 07:51:57 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE LINK_MasterDuties_Lables (
    MasterDutiesLabelsID int IDENTITY(1,1) PRIMARY KEY,
    MasterDutyId int NOT NULL,
    LabelId int not null,
    IsActive bit not null default 1
)