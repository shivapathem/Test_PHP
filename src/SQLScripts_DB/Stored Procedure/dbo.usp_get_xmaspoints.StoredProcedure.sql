USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_xmaspoints]    Script Date: 29/09/2025 20:04:45 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER     PROCEDURE [dbo].[usp_get_xmaspoints]
@strScheduledPersonID varchar(max)

AS
BEGIN


SET NOCOUNT ON;

DECLARE	@query  AS NVARCHAR(MAX)

SET @query ='Select X.Points, LOWER(S.UD_NetLogin) AS Login from XmasPoints (nolock) X 
    INNER JOIN UserDetails (nolock) S ON S.UD_UserID=X.ScheduledPersonID
    WHERE (X.ScheduledPersonID IN ('+@strScheduledPersonID+'))
    AND S.UD_NetLogin is not null';
	
	exec sp_executesql @query
END