USE [Allocate7]
GO

/****** Object:  Table [dbo].[ChargingDutyMapping_Link]    Script Date: 13/01/2022 18:09:28 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF  NOT EXISTS (SELECT 1 FROM sys.objects
WHERE object_id = OBJECT_ID(N'[dbo].[ChargingDutyMapping_Link]'))
BEGIN
CREATE TABLE [dbo].[ChargingDutyMapping_Link](
    [ChargingId] [int] IDENTITY(1,1) NOT NULL,
    [EstabCodeId] [int] NOT NULL,
    [ActivityCodeId] [int] NOT NULL,
    [MasterDutyId] [int] NOT NULL,
    [AllocationId] [int] NOT NULL,
    [ChargeCodeId] [int] NOT NULL,
    [Quantity] [int] NOT NULL,
    [UnitPrice] [float] NOT NULL,
    [Comments] [varchar](100) NULL,
    [Contact] [varchar](50) NULL,
    [Telephone] [varchar](50) NULL,
    [IsActual] [bit] NOT NULL,
    [ChargingDutyDate] [datetime] NOT NULL,
    [IsSentToFinance] [bit] NULL,
    [PersonId] [int] NULL,
    [StaffId] [int] NULL,
    [CreatedBy] [int] NULL,
    [CreatedDate] [datetime] NULL,
    [ModifiedBy] [int] NULL,
    [ModifiedDate] [datetime] NULL,
    [SChedulingTeamId] [int] NULL,
    [SentToFinanceDate] [datetime] NULL,
    CONSTRAINT [PK_ChargingDutyMapping_Link] PRIMARY KEY CLUSTERED
(
[ChargingId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
    ) ON [PRIMARY]
END
GO

