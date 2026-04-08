USE [Allocate7]
GO

/****** Object:  Table [dbo].[PublicHolidays]    Script Date: 19/08/2021 20:21:19 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

SET ANSI_PADDING ON
GO
IF NOT EXISTS (SELECT * 
       FROM INFORMATION_SCHEMA.TABLES 
      WHERE TABLE_SCHEMA = 'dbo' 
        AND  TABLE_NAME = 'PublicHolidays')
BEGIN

CREATE TABLE [dbo].[PublicHolidays](
              [PublicHolidayId] [int] IDENTITY(1,1) NOT NULL,
              [CalenderYear] [numeric](18, 0) NOT NULL,
              [HolidayDate] [date] NOT NULL,
              [Week] [varchar](50) NULL,
              [Description] [nvarchar](max) NOT NULL,
              [IsActive] [int] NOT NULL,
              [CreatedBy] [int] NULL,
              [ModifiedBy] [int] NULL,
              [CreatedDate] [datetime] NOT NULL,
              [ModifiedDate] [datetime] NULL,
              [IsDeleted] [int] NULL,
CONSTRAINT [PK_PublicHolidays] PRIMARY KEY CLUSTERED 
(
              [PublicHolidayId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]


END

SET ANSI_PADDING OFF
GO

