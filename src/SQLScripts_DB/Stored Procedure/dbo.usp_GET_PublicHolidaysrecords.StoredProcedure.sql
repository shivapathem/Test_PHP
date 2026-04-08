USE [ALLOCATE7]

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

--Declare variable
DECLARE @strSQL NVARCHAR(max)

--Check if the stored procedure already exists or not so we can either use ALTER or CREATE for the script
IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_GET_PublicHolidaysrecords]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N'PROCEDURE [dbo].[usp_GET_PublicHolidaysrecords]
@id INT
AS
BEGIN
              -- SET NOCOUNT ON added to prevent extra result sets from
              -- interfering with SELECT statements.
              SET NOCOUNT ON;
                             IF @id = 0
                             SET @id = NULL
                             IF @id IS NOT NULL
                                           BEGIN
                                                          Select PublicHolidayId,CalenderYear,convert(varchar, HolidayDate, 105) as HolidayDate,[Week],[Description],IsActive
                                                                                      from PublicHolidays 
                                                                                      WHERE IsDeleted =0 AND PublicHolidayId = @id
                                                                                      Order By PublicHolidayId desc
                                           END       
                             ELSE
                                     Select PublicHolidayId,CalenderYear,convert(varchar, HolidayDate, 105) as HolidayDate ,[Week],[Description],IsActive
                                                                                      from PublicHolidays 
                                                                                      WHERE IsDeleted =0 
                                                                                      Order By PublicHolidayId desc
END
'

EXEC dbo.sp_executesql @strSQL

GO