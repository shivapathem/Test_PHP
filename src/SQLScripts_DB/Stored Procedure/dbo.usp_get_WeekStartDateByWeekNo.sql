USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_WeekStartDateByWeekNo]    Script Date: 13/06/2022 17:27:01 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER  PROCEDURE [dbo].[usp_get_WeekStartDateByWeekNo] 
	-- Add the parameters for the stored procedure here
	@weekno int,
	@task varchar(20),
	@iDayoftheWeek int
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

    -- Select statements for procedure here
	IF(@task='ByweeknoOnly')
	BEGIN
		SELECT TOP 1 dDateTime from TimeDimension (nolock) where ixYearWeek = @weekno order by dDateTime ASC
	END
	ELSE
	BEGIN
		SELECT TOP 1 dDateTime from TimeDimension (nolock) where ixYearWeek = @weekno  AND ixDayInWeek=@iDayoftheWeek order by dDateTime ASC
	END
END
